<?php

declare(strict_types=1);

/*
 * SPDX-FileCopyrightText: 2026 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace Nextcloud\Rector\Rector;

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
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

use function array_keys;
use function is_string;

final class ReplaceIConfigWithIAppConfigRector extends AbstractRector
{
    private const APP_CONFIG_CLASS = 'OCP\IAppConfig';

    /**
     * Map of deprecated \OCP\IConfig methods to their \OCP\IAppConfig replacements.
     * The argument lists are forwarded as-is; the new methods accept additional
     * optional parameters that default to a behaviour matching the old methods.
     */
    private const METHOD_MAP = [
        'getAppValue' => 'getValue',
        'getAppKeys' => 'getKeys',
        'setAppValue' => 'setValue',
        'deleteAppValue' => 'deleteKey',
        'deleteAppValues' => 'deleteApp',
    ];

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Replace deprecated \OCP\IConfig app-config methods with their \OCP\IAppConfig counterparts,'
            . ' injecting IAppConfig alongside the existing IConfig.',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
use OCP\IConfig;

class SomeClass
{
    public function __construct(private IConfig $config) {}

    public function run(): string
    {
        return $this->config->getAppValue('myapp', 'mykey', 'default');
    }
}
CODE_SAMPLE,
                    <<<'CODE_SAMPLE'
use OCP\IAppConfig;
use OCP\IConfig;

class SomeClass
{
    public function __construct(private IConfig $config, private IAppConfig $appConfig) {}

    public function run(): string
    {
        return $this->appConfig->getValueString('myapp', 'mykey', 'default');
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
        return [Class_::class];
    }

    public function refactor(Node $node): ?Node
    {
        if (!($node instanceof Class_)) {
            return null;
        }

        $constructor = $node->getMethod(MethodName::CONSTRUCT);
        if (!$constructor instanceof ClassMethod) {
            return null;
        }

        $iConfigPropertyNames = $this->collectIConfigPromotedPropertyNames($constructor);
        if ($iConfigPropertyNames === []) {
            return null;
        }

        $callsToRewrite = $this->collectDeprecatedAppConfigCalls($node, $iConfigPropertyNames);
        if ($callsToRewrite === []) {
            return null;
        }

        $appConfigName = $this->findExistingAppConfigPropertyName($constructor);
        if ($appConfigName === null) {
            $appConfigName = $this->makeUniquePropertyName($constructor, 'appConfig');
            $constructor->params[] = $this->buildPromotedAppConfigParam($appConfigName);
        }

        foreach ($callsToRewrite as $call) {
            $oldMethodName = $this->getName($call->name);
            if ($oldMethodName === null || !isset(self::METHOD_MAP[$oldMethodName])) {
                continue;
            }
            /** @var PropertyFetch $propertyFetch */
            $propertyFetch = $call->var;
            $propertyFetch->name = new Identifier($appConfigName);
            $call->name = new Identifier(self::METHOD_MAP[$oldMethodName]);
        }

        return $node;
    }

    /**
     * @return list<string>
     */
    private function collectIConfigPromotedPropertyNames(ClassMethod $constructor): array
    {
        $names = [];
        foreach ($constructor->getParams() as $param) {
            if ($param->flags === 0) {
                continue;
            }
            if (!$this->isObjectType($param, new ObjectType('OCP\IConfig'))) {
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
    private function collectDeprecatedAppConfigCalls(Class_ $class, array $propertyNames): array
    {
        $deprecatedMethods = array_keys(self::METHOD_MAP);
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

    private function findExistingAppConfigPropertyName(ClassMethod $constructor): ?string
    {
        foreach ($constructor->getParams() as $param) {
            if ($param->flags === 0) {
                continue;
            }
            if (!$this->isObjectType($param, new ObjectType(self::APP_CONFIG_CLASS))) {
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

    private function buildPromotedAppConfigParam(string $name): Param
    {
        return new Param(
            new Variable($name),
            null,
            new FullyQualified('OCP\IAppConfig'),
            false,
            false,
            [],
            Modifiers::PRIVATE,
        );
    }
}
