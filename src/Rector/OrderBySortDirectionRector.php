<?php

declare(strict_types=1);

/*
 * SPDX-FileCopyrightText: 2026 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace Nextcloud\Rector\Rector;

use Override;
use PHPStan\Type\ObjectType;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Scalar\String_;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

use function strtolower;

final class OrderBySortDirectionRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Replace the \'ASC\'/\'DESC\' string argument of IQueryBuilder::orderBy() and ::addOrderBy() '
            . 'with the \SortDirection enum',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
$query->orderBy('name', 'ASC');
$query->addOrderBy('name', 'DESC');
CODE_SAMPLE,
                    <<<'CODE_SAMPLE'
$query->orderBy('name', \SortDirection::Ascending);
$query->addOrderBy('name', \SortDirection::Descending);
CODE_SAMPLE,
                ),
            ],
        );
    }

    /**
     * @return array<class-string<Node>>
     */
    #[Override]
    public function getNodeTypes(): array
    {
        return [MethodCall::class];
    }

    #[Override]
    public function refactor(Node $node): ?Node
    {
        if (!($node instanceof MethodCall)) {
            return null;
        }

        if (!$this->isNames($node->name, ['orderBy', 'addOrderBy'])) {
            return null;
        }

        if (!$this->isObjectType($node->var, new ObjectType('OCP\DB\QueryBuilder\IQueryBuilder'))) {
            return null;
        }

        $orderArg = $this->resolveOrderArg($node->getArgs());
        if (!$orderArg instanceof Arg) {
            return null;
        }

        if (!$orderArg->value instanceof String_) {
            return null;
        }

        $enumCase = match (strtolower($orderArg->value->value)) {
            'asc' => 'Ascending',
            'desc' => 'Descending',
            default => null,
        };

        if ($enumCase === null) {
            return null;
        }

        $orderArg->value = new ClassConstFetch(new FullyQualified('SortDirection'), $enumCase);

        return $node;
    }

    /**
     * @param Arg[] $args
     */
    private function resolveOrderArg(array $args): ?Arg
    {
        $position = 0;
        foreach ($args as $arg) {
            if ($arg->name !== null) {
                if ($this->isName($arg->name, 'order')) {
                    return $arg;
                }

                continue;
            }

            if ($position === 1) {
                return $arg;
            }

            ++$position;
        }

        return null;
    }
}
