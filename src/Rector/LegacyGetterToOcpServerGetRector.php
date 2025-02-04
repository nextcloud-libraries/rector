<?php

declare(strict_types=1);

/*
 * SPDX-FileCopyrightText: 2024 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-only
 */

namespace Nextcloud\Rector\Rector;

use InvalidArgumentException;
use Nextcloud\Rector\ValueObject\LegacyGetterToOcpServerGet;
use PHPStan\Type\ObjectType;
use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticPropertyFetch;
use Rector\Contract\Rector\ConfigurableRectorInterface;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

final class LegacyGetterToOcpServerGetRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var LegacyGetterToOcpServerGet[]
     */
    private array $legacyGetterToOcpServerGet = [];

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Replace legacy getter calls on \OC::$server to desired static call on \OCP\Server',
            [
                new ConfiguredCodeSample(<<<'CODE_SAMPLE'
\OC::$server->getLogger()->debug('debug log');
CODE_SAMPLE
            , <<<'CODE_SAMPLE'
\OCP::Server::get(\Psr\Log\LoggerInterface::class)->debug('debug log');
CODE_SAMPLE
            , [new LegacyGetterToOcpServerGet('getLogger', '\Psr\Log\LoggerInterface')]),
            ],
        );
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [MethodCall::class];
    }

    public function refactor(Node $node): ?Node
    {
        if (!($node instanceof MethodCall)) {
            return null;
        }
        if (!$node->var instanceof StaticPropertyFetch) {
            return null;
        }
        if (!$this->isName($node->var->name, 'server')) {
            return null;
        }
        if (!$this->isObjectType($node->var->class, new ObjectType('OC'))) {
            return null;
        }
        foreach ($this->legacyGetterToOcpServerGet as $config) {
            if (!$this->isName($node->name, $config->getOldMethod())) {
                continue;
            }
            $factoryMethod = $config->getFactoryMethod();
            if ($factoryMethod === null) {
                if ($node->args !== []) {
                    /* Skip if there are parameters to the call as they would be lost by the migration */
                    return null;
                }

                return $this->nodeFactory->createStaticCall(
                    'OCP\Server',
                    'get',
                    [$this->nodeFactory->createClassConstReference($config->getNewClass())],
                );
            } else {
                return $this->nodeFactory->createMethodCall(
                    $this->nodeFactory->createStaticCall(
                        'OCP\Server',
                        'get',
                        [$this->nodeFactory->createClassConstReference($config->getNewClass())],
                    ),
                    $factoryMethod,
                    $node->args,
                );
            }
        }

        return null;
    }

    /**
     * @param mixed[] $configuration
     */
    public function configure(array $configuration): void
    {
        foreach ($configuration as $config) {
            if (!$config instanceof LegacyGetterToOcpServerGet) {
                throw new InvalidArgumentException('Only supports LegacyGetterToOcpServerGet configurations');
            }
        }
        /**
         * @psalm-suppress MixedPropertyTypeCoercion
         * @phpstan-ignore assign.propertyType
         */
        $this->legacyGetterToOcpServerGet = $configuration;
    }
}
