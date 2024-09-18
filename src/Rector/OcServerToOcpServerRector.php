<?php

declare(strict_types=1);

/*
 * SPDX-FileCopyrightText: 2024 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-only
 */

namespace ChristophWurst\Nextcloud\Rector\Rector;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\Name\FullyQualified;
use Rector\CodingStyle\Application\UseImportsAdder;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

class OcServerToOcpServerRector extends AbstractRector
{
    public function __construct(
        private UseImportsAdder $useImportsAdder,
    ) {
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
        $methodCallName = $this->getName($node->name);
        if ($methodCallName === null) {
            return null;
        }

        if (
            $methodCallName !== 'get'
            || !($node->var instanceof StaticPropertyFetch)
        ) {
            return null;
        }
        $class = $node->var->class;
        if (
            !($class instanceof FullyQualified)
            || $class->getParts() !== ['OC']
        ) {
            return null;
        }

        return new StaticCall(
            new FullyQualified('OCP\Server'),
            'get',
            $node->args,
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
