<?php

declare(strict_types=1);

/*
 * SPDX-FileCopyrightText: 2024 Daniel Kesselberg <mail@danielkesselberg.de>
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace ChristophWurst\Nextcloud\Rector\Rector;

use PhpParser\Node;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Class_;
use Rector\Rector\AbstractRector;
use Rector\ValueObject\MethodName;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

use function count;

class RenameUserIdRector extends AbstractRector
{
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [Class_::class];
    }

    public function refactor(Node $node): ?Node
    {
        if (!($node instanceof Class_)) {
            return null;
        }

        $hasChanged = \false;

        $extendsController = $node->extends instanceof FullyQualified
            && $node->extends->toString() === 'OCP\AppFramework\Controller';

        $implementsSettingsInterface = false;
        foreach ($node->implements as $interface) {
            if ($interface instanceof FullyQualified && $interface->toString() === 'OCP\Settings\ISettings') {
                $implementsSettingsInterface = true;
            }
        }

        if (!$extendsController && !$implementsSettingsInterface) {
            return null;
        }

        $constructClassMethod = $node->getMethod(MethodName::CONSTRUCT);
        if (!$constructClassMethod instanceof ClassMethod) {
            return null;
        }

        $params = $constructClassMethod->getParams();
        if (count($params) === 0) {
            return null;
        }

        foreach ($params as $param) {
            if ($param->var instanceof Variable && $param->var->name === 'UserId') {
                $param->var->name = 'userId';
                $hasChanged = \true;
            }
        }

        if (!$hasChanged) {
            return null;
        }

        return $node;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Change $UserId to $userId for controller or settings classes',
            [
                new CodeSample(
                    'public function __construct($UserId)',
                    'public function __construct($userId)',
                ),
            ],
        );
    }
}
