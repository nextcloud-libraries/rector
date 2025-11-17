<?php

declare(strict_types=1);

/*
 * SPDX-FileCopyrightText: 2025 Nextcloud GmbH
 * SPDX-FileContributor: Carl Schwan
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace Nextcloud\Rector\Rector;

use PHPStan\Type\ObjectType;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

use function is_string;

final class ReplaceFetchAllMethodCallRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Change \OCP\DB\IResult->fetchAll() to ->fetchAllAssociative() and other replacements',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
use OCP\DB\IResult;
use OCP\DB\IConnection;
use OCP\DB\IQueryBuilder;

class SomeClass
{
    public function run(IConnection $connection)
    {
        $qb = $connection->getQueryBuilder();
        $result = $qb->exec();
        return $result->fetchAll();
    }
}
CODE_SAMPLE,
                    <<<'CODE_SAMPLE'
use OCP\DB\IResult;
use OCP\DB\IConnection;
use OCP\DB\IQueryBuilder;

class SomeClass
{
    public function run(IConnection $connection)
    {
        $qb = $connection->getQueryBuilder();
        $result = $qb->exec();
        return $result->fetchAllAssociative();
    }
}
CODE_SAMPLE,
                ),
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
        if ($this->isObjectType($node->var, new ObjectType('OCP\DB\IResult'))) {
            return $this->refactorResultStatement($node);
        }

        return null;
    }

    private function refactorResultStatement(MethodCall $methodCall): ?MethodCall
    {
        if ($this->isName($methodCall->name, 'fetchColumn')) {
            $methodCall->name = new Identifier('fetchOne');

            return $methodCall;
        }

        if ($this->isName($methodCall->name, 'fetch')) {
            $args = $methodCall->getArgs();
            if ($args === []) {
                $methodCall->name = new Identifier('fetchAssociative');

                return $methodCall;
            }

            $firstArg = $args[0];

            $newMethodName = $this->resolveFirstMethodName($firstArg, false);
            if (is_string($newMethodName)) {
                $methodCall->args = [];

                $methodCall->name = new Identifier($newMethodName);

                return $methodCall;
            }

            return null;
        }

        if ($this->isName($methodCall->name, 'fetchAll')) {
            $args = $methodCall->getArgs();
            if ($args === []) {
                $methodCall->name = new Identifier('fetchAllAssociative');

                return $methodCall;
            }

            $firstArg = $args[0];

            $newMethodName = $this->resolveFirstMethodName($firstArg, true);
            if (is_string($newMethodName)) {
                $methodCall->args = [];

                $methodCall->name = new Identifier($newMethodName);

                return $methodCall;
            }
        }

        return null;
    }

    private function resolveFirstMethodName(Arg $firstArg, bool $all = false): ?string
    {
        if (!$firstArg->value instanceof ClassConstFetch) {
            return null;
        }

        $classConstFetch = $firstArg->value;
        if (!$this->isName($classConstFetch->class, 'PDO')) {
            return null;
        }

        if ($this->isName($classConstFetch->name, 'FETCH_COLUMN')) {
            return $all ? 'fetchFirstColumn' : 'fetchOne';
        }

        if ($this->isName($classConstFetch->name, 'FETCH_ASSOC')) {
            return $all ? 'fetchAllAssociative' : 'fetchAssociative';
        }

        if ($this->isName($classConstFetch->name, 'FETCH_NUM')) {
            return $all ? 'fetchAllNumeric' : 'fetchNumeric';
        }

        return null;
    }
}
