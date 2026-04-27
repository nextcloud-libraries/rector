<?php

declare(strict_types=1);

/*
 * SPDX-FileCopyrightText: 2026 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace Nextcloud\Rector\Rector;

use Override;
use PHPStan\Type\ObjectType;
use PhpParser\Modifiers;
use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Class_;
use Rector\Rector\AbstractRector;
use Rector\ValueObject\MethodName;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

use function array_keys;
use function is_string;

abstract class AReplaceClassRector extends AbstractRector
{
    abstract public function getOldClassName(): string;

    abstract public function getNewClassName(): string;

    abstract public function getDesiredVarName(): string;

    /**
     * @return array<string, string>
     */
    abstract public function getMethodMap(): array;

    abstract public function getRuleDefinition(): RuleDefinition;

    protected function getNewMethod(?string $oldMethod): ?string
    {
        if ($oldMethod === null) {
            return null;
        }

        return $this->getMethodMap()[$oldMethod] ?? null;
    }

    /**
     * @return array<class-string<Node>>
     */
    #[Override]
    public function getNodeTypes(): array
    {
        return [Class_::class];
    }

    #[Override]
    public function refactor(Node $node): ?Node
    {
        if (!($node instanceof Class_)) {
            return null;
        }

        $constructor = $node->getMethod(MethodName::CONSTRUCT);
        if (!$constructor instanceof ClassMethod) {
            return null;
        }

        $iConfigPropertyNames = $this->collectOldPromotedPropertyNames($constructor);
        if ($iConfigPropertyNames === []) {
            return null;
        }

        $callsToRewrite = $this->collectDeprecatedCalls($node, $iConfigPropertyNames);
        if ($callsToRewrite === []) {
            return null;
        }

        $appConfigName = $this->findExistingPropertyName($constructor);
        if ($appConfigName === null) {
            $appConfigName = $this->makeUniquePropertyName($constructor, $this->getDesiredVarName());
            $constructor->params[] = $this->buildPromotedParam($appConfigName);
        }

        foreach ($callsToRewrite as $call) {
            $oldMethodName = $this->getName($call->name);
            $newMethodName = $this->getNewMethod($oldMethodName);
            if ($oldMethodName === null || $newMethodName === null) {
                continue;
            }
            /** @var PropertyFetch $propertyFetch */
            $propertyFetch = $call->var;
            $propertyFetch->name = new Identifier($appConfigName);
            $call->name = new Identifier($newMethodName);
        }

        return $node;
    }

    /**
     * @return list<string>
     */
    private function collectOldPromotedPropertyNames(ClassMethod $constructor): array
    {
        $names = [];
        foreach ($constructor->getParams() as $param) {
            if ($param->flags === 0) {
                continue;
            }
            if (!$this->isObjectType($param, new ObjectType($this->getOldClassName()))) {
                continue;
            }
            $name = $this->getName($param->var);
            if (is_string($name)) {
                $names[] = $name;
            }
        }

        return $names;
    }

    /**
     * @param list<string> $propertyNames
     *
     * @return list<MethodCall>
     */
    private function collectDeprecatedCalls(Class_ $class, array $propertyNames): array
    {
        $deprecatedMethods = array_keys($this->getMethodMap());
        $calls = [];
        foreach ($class->getMethods() as $classMethod) {
            $stmts = $classMethod->getStmts();
            if ($stmts === null) {
                continue;
            }
            $this->traverseNodesWithCallable(
                $stmts,
                function (Node $subNode) use ($propertyNames, $deprecatedMethods, &$calls): ?Node {
                    if (!$subNode instanceof MethodCall) {
                        return null;
                    }
                    if (!$this->isNames($subNode->name, $deprecatedMethods)) {
                        return null;
                    }
                    if (!$subNode->var instanceof PropertyFetch) {
                        return null;
                    }
                    $propertyFetch = $subNode->var;
                    if (!$propertyFetch->var instanceof Variable) {
                        return null;
                    }
                    if (!$this->isName($propertyFetch->var, 'this')) {
                        return null;
                    }
                    if ($this->isNames($propertyFetch->name, $propertyNames)) {
                        $calls[] = $subNode;
                    }

                    return null;
                },
            );
        }

        return $calls;
    }

    private function findExistingPropertyName(ClassMethod $constructor): ?string
    {
        foreach ($constructor->getParams() as $param) {
            if ($param->flags === 0) {
                continue;
            }
            if (!$this->isObjectType($param, new ObjectType($this->getNewClassName()))) {
                continue;
            }
            $name = $this->getName($param->var);
            if (is_string($name)) {
                return $name;
            }
        }

        return null;
    }

    private function makeUniquePropertyName(ClassMethod $constructor, string $desired): string
    {
        $taken = [];
        foreach ($constructor->getParams() as $param) {
            $name = $this->getName($param->var);
            if (is_string($name)) {
                $taken[$name] = true;
            }
        }
        if (!isset($taken[$desired])) {
            return $desired;
        }
        $i = 2;
        while (isset($taken[$desired . $i])) {
            $i++;
        }

        return $desired . $i;
    }

    private function buildPromotedParam(string $name): Param
    {
        return new Param(
            new Variable($name),
            null,
            new FullyQualified($this->getNewClassName()),
            false,
            false,
            [],
            Modifiers::PRIVATE,
        );
    }
}
