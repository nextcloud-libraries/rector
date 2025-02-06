<?php

declare(strict_types=1);

/*
 * SPDX-FileCopyrightText: 2024 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-only
 */

namespace Nextcloud\Rector\Rector;

use PHPStan\Type\ObjectType;
use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\Name\FullyQualified;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

class OcServerToOcpServerRector extends AbstractRector
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
        if (!$node->var instanceof StaticPropertyFetch) {
            return null;
        }
        if (!$this->isName($node->var->name, 'server')) {
            return null;
        }

        $methodCallName = $this->getName($node->name);
        if ($methodCallName !== 'get' && $methodCallName !== 'query') {
            return null;
        }

        if (!$this->isObjectType($node->var->class, new ObjectType('OC'))) {
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
            'Change method calls from OC::$server->get and OC::$server->query to OCP\Server::get.',
            [
                new CodeSample(
                    '\OC::$server->get(IAppConfig::class);',
                    '\OCP\Server::get(IAppConfig::class);',
                ),
            ],
        );
    }
}
