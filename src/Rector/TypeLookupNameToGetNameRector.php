<?php

declare(strict_types=1);

/*
 * SPDX-FileCopyrightText: 2026 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace Nextcloud\Rector\Rector;

use Override;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp;
use PhpParser\Node\Expr\BinaryOp\Equal;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BinaryOp\NotEqual;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Scalar\String_;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

use function count;
use function strtolower;

final class TypeLookupNameToGetNameRector extends AbstractRector
{
    /**
     * Marks a `->getName()` MethodCall as one we created from a `Type::lookupName()` static call,
     * so the comparison rewrite below can safely recognize it without re-matching by shape.
     */
    private const IS_COLUMN_TYPE_GET_NAME = 'nextcloud_is_column_type_get_name';

    private const CONST_NAME_TO_CASE = [
        'BIGINT' => 'Bigint',
        'BINARY' => 'Binary',
        'BLOB' => 'Blob',
        'BOOLEAN' => 'Boolean',
        'DATE' => 'Date',
        'DATE_IMMUTABLE' => 'DateImmutable',
        'DATETIME' => 'Datetime',
        'DATETIME_IMMUTABLE' => 'DatetimeImmutable',
        'DATETIME_TZ' => 'DatetimeTz',
        'DATETIME_TZ_IMMUTABLE' => 'DatetimeTzImmutable',
        'DECIMAL' => 'Decimal',
        'FLOAT' => 'Float',
        'INTEGER' => 'Integer',
        'SMALLINT' => 'Smallint',
        'STRING' => 'String',
        'TEXT' => 'Text',
        'TIME' => 'Time',
        'TIME_IMMUTABLE' => 'TimeImmutable',
        'JSON' => 'Json',
    ];

    private const VALUE_TO_CASE = [
        'bigint' => 'Bigint',
        'binary' => 'Binary',
        'blob' => 'Blob',
        'boolean' => 'Boolean',
        'date' => 'Date',
        'date_immutable' => 'DateImmutable',
        'datetime' => 'Datetime',
        'datetime_immutable' => 'DatetimeImmutable',
        'datetimetz' => 'DatetimeTz',
        'datetimetz_immutable' => 'DatetimeTzImmutable',
        'decimal' => 'Decimal',
        'float' => 'Float',
        'integer' => 'Integer',
        'smallint' => 'Smallint',
        'string' => 'String',
        'text' => 'Text',
        'time' => 'Time',
        'time_immutable' => 'TimeImmutable',
        'json' => 'Json',
    ];

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Replace \Doctrine\DBAL\Types\Type::lookupName($column->getType()) with $column->getType()->getName(), '
            . 'or directly with the \OCP\DB\Schema\ColumnType enum case when compared against a known column type',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
use Doctrine\DBAL\Types\Type;
use OCP\DB\Types;

Type::lookupName($column->getType());
Type::lookupName($column->getType()) === Types::STRING;
CODE_SAMPLE,
                    <<<'CODE_SAMPLE'
use OCP\DB\Schema\ColumnType;

$column->getType()->getName();
$column->getType() === ColumnType::String;
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
        return [StaticCall::class, Identical::class, NotIdentical::class, Equal::class, NotEqual::class];
    }

    #[Override]
    public function refactor(Node $node): ?Node
    {
        if ($node instanceof StaticCall) {
            return $this->refactorStaticCall($node);
        }

        if ($node instanceof BinaryOp) {
            return $this->refactorComparison($node);
        }

        return null;
    }

    private function refactorStaticCall(StaticCall $staticCall): ?MethodCall
    {
        if (!$this->isName($staticCall->class, 'Doctrine\DBAL\Types\Type')) {
            return null;
        }

        if (!$this->isName($staticCall->name, 'lookupName')) {
            return null;
        }

        $args = $staticCall->getArgs();
        if (count($args) !== 1) {
            return null;
        }

        $methodCall = new MethodCall($args[0]->value, 'getName');
        $methodCall->setAttribute(self::IS_COLUMN_TYPE_GET_NAME, true);

        return $methodCall;
    }

    /**
     * If one side is a `->getName()` call we created above and the other side is a recognizable
     * column type (a `Types::*`/`ColumnType::*` constant or a matching string literal), compare
     * the `ColumnType` enum instances directly instead of going through their string names.
     */
    private function refactorComparison(BinaryOp $binaryOp): ?BinaryOp
    {
        if ($this->isColumnTypeGetNameCall($binaryOp->left)) {
            $caseName = $this->resolveColumnTypeCaseName($binaryOp->right);
            if ($caseName === null) {
                return null;
            }

            /** @var MethodCall $left */
            $left = $binaryOp->left;
            $binaryOp->left = $left->var;
            $binaryOp->right = new ClassConstFetch(new FullyQualified('OCP\DB\Schema\ColumnType'), $caseName);

            return $binaryOp;
        }

        if ($this->isColumnTypeGetNameCall($binaryOp->right)) {
            $caseName = $this->resolveColumnTypeCaseName($binaryOp->left);
            if ($caseName === null) {
                return null;
            }

            /** @var MethodCall $right */
            $right = $binaryOp->right;
            $binaryOp->right = $right->var;
            $binaryOp->left = new ClassConstFetch(new FullyQualified('OCP\DB\Schema\ColumnType'), $caseName);

            return $binaryOp;
        }

        return null;
    }

    private function isColumnTypeGetNameCall(Expr $expr): bool
    {
        return $expr instanceof MethodCall && $expr->getAttribute(self::IS_COLUMN_TYPE_GET_NAME) === true;
    }

    private function resolveColumnTypeCaseName(Expr $expr): ?string
    {
        if ($expr instanceof String_) {
            return self::VALUE_TO_CASE[strtolower($expr->value)] ?? null;
        }

        if (!($expr instanceof ClassConstFetch) || !($expr->name instanceof Identifier)) {
            return null;
        }

        if ($this->isName($expr->class, 'OCP\DB\Schema\ColumnType')) {
            return $expr->name->toString();
        }

        if ($this->isName($expr->class, 'OCP\DB\Types')) {
            return self::CONST_NAME_TO_CASE[$expr->name->toString()] ?? null;
        }

        return null;
    }
}
