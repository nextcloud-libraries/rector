<?php

declare(strict_types=1);

/*
 * SPDX-FileCopyrightText: 2024 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace Nextcloud\Rector\Rector;

use InvalidArgumentException;
use Nextcloud\Rector\ValueObject\RenameParameter;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Expression;
use Rector\Contract\Rector\ConfigurableRectorInterface;
use Rector\Rector\AbstractRector;
use Rector\Reflection\ReflectionResolver;
use Rector\ValueObject\MethodName;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

use function count;
use function is_array;

/** @psalm-suppress PropertyNotSetInConstructor */
class RenameParameterRector extends AbstractRector implements ConfigurableRectorInterface
{
    /** @var RenameParameter[] */
    private array $renameParameters = [];

    public function __construct(
        private ReflectionResolver $reflectionResolver,
    ) {
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Rename parameter for controller or settings classes',
            [
                new CodeSample(
                    'public function __construct($UserId)',
                    'public function __construct($userId)',
                ),
            ],
        );
    }

    /**
     * @param mixed[] $configuration
     */
    public function configure(array $configuration): void
    {
        foreach ($configuration as $renameParameter) {
            if (!$renameParameter instanceof RenameParameter) {
                throw new InvalidArgumentException('Only supports RenameParameter configurations');
            }
            $this->renameParameters[] = $renameParameter;
        }
    }

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

        $classReflection = $this->reflectionResolver->resolveClassReflection($node);
        if ($classReflection === null) {
            return null;
        }

        $extendsController = $classReflection->is('OCP\AppFramework\Controller');
        $implementsSettingsInterface = $classReflection->implementsInterface('OCP\Settings\ISettings');

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

        $hasChanged = false;

        foreach ($this->renameParameters as $renameParameter) {
            foreach ($params as $param) {
                if ($this->renameVariable($param->var, $renameParameter)) {
                    $hasChanged = true;
                }
            }
        }

        if (!$hasChanged) {
            return null;
        }

        $stmts = $constructClassMethod->getStmts();
        if (!is_array($stmts)) {
            return $node;
        }

        foreach ($stmts as $stmt) {
            if (!($stmt instanceof Expression)) {
                continue;
            }

            foreach ($this->renameParameters as $renameParameter) {
                $this->renameAssignments($stmt->expr, $renameParameter);
                $this->renameMethodOrStaticCalls($stmt->expr, $renameParameter);
            }
        }

        return $node;
    }

    private function renameAssignments(Expr $expr, RenameParameter $renameParameter): void
    {
        if ($expr instanceof Assign) {
            $this->renameVariable($expr->expr, $renameParameter);
        }
    }

    private function renameMethodOrStaticCalls(Expr $expr, RenameParameter $renameParameter): void
    {
        if (!($expr instanceof MethodCall) && !($expr instanceof StaticCall)) {
            return;
        }

        foreach ($expr->getArgs() as $arg) {
            $this->renameVariable($arg->value, $renameParameter);
        }
    }

    private function renameVariable(Expr $variable, RenameParameter $renameParameter): bool
    {
        if ($variable instanceof Variable && $variable->name === $renameParameter->getOldName()) {
            $variable->name = $renameParameter->getNewName();

            return true;
        }

        return false;
    }
}
