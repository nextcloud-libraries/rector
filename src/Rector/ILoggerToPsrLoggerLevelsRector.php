<?php

declare(strict_types=1);

/*
 * SPDX-FileCopyrightText: 2024 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-only
 */

namespace ChristophWurst\Nextcloud\Rector\Rector;

use PhpParser\Node;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name\FullyQualified;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

use function array_slice;

class ILoggerToPsrLoggerLevelsRector extends AbstractRector
{
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
        $methodCallName = $this->getName($node->name);
        if ($methodCallName !== 'log') {
            return null;
        }
        if (
            !($node->args[0] instanceof Node\Arg)
            || !($node->args[0]->value instanceof ClassConstFetch)
            || $this->getName($node->args[0]->value->class) !== 'OCP\ILogger'
        ) {
            return null;
        }

        return new MethodCall(
            $node->var,
            'log',
            [
                new Node\Arg(new ClassConstFetch(
                    new FullyQualified('Psr\Log\LogLevel'),
                    new Identifier(match ($this->getName($node->args[0]->value->name)) {
                        'DEBUG' => 'DEBUG',
                        'INFO' => 'INFO',
                        'WARN' => 'WARNING',
                        'FATAL' => 'EMERGENCY',
                        default => 'ERROR',
                    }),
                )),
                ...array_slice($node->args, 1),
            ],
        );
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Change method calls from set* to change*.',
            [
                new CodeSample(
                    '\OC::$server->get(IAppConfig::class);',
                    '\OCP\Server::get(IAppConfig::class);',
                ),
            ],
        );
    }
}
