<?php

declare(strict_types=1);

/*
 * SPDX-FileCopyrightText: 2026 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace Nextcloud\Rector\Rector;

use Nextcloud\Rector\ValueObject\ReplaceInjectedMethodCall;
use Override;
use PHPStan\Type\ObjectType;
use PhpParser\Modifiers;
use PhpParser\Node;
use PhpParser\Node\Expr\ArrowFunction;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Property;
use Rector\Contract\Rector\ConfigurableRectorInterface;
use Rector\Exception\Configuration\InvalidConfigurationException;
use Rector\Rector\AbstractRector;
use Rector\ValueObject\MethodName;
use Rector\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use Webmozart\Assert\Assert;

use function array_keys;
use function is_string;
use function sprintf;

/** @psalm-suppress PropertyNotSetInConstructor */
final class ReplaceInjectedMethodCallRector extends AbstractRector implements
    ConfigurableRectorInterface,
    MinPhpVersionInterface
{
    /**
     * @var ReplaceInjectedMethodCall[]
     */
    private array $replaceInjectedMethodCall = [];

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Replace injected method calls',
            [
                new ConfiguredCodeSample(
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
CODE_SAMPLE
                    ,
                    [
                        new ReplaceInjectedMethodCall(
                            'OCP\IConfig',
                            'OCP\IAppConfig',
                            'appConfig',
                            ['getAppValue' => 'getValueString'],
                        ),
                    ],
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
        return [
            Class_::class, Property::class, Param::class, ClassMethod::class,
            Function_::class, Closure::class, ArrowFunction::class, Interface_::class,
        ];
    }

    /**
     * @param Class_|Property|Param|ClassMethod|Function_|Closure|ArrowFunction|Interface_ $node
     *
     * @psalm-suppress MoreSpecificImplementedParamType
     */
    #[Override]
    public function refactor(Node $node): ?Node
    {
        if ($this->replaceInjectedMethodCall === []) {
            throw new InvalidConfigurationException(sprintf('The "%s" rule requires configuration.', self::class));
        }

        $refactoredNode = null;
        foreach ($this->replaceInjectedMethodCall as $replaceInjectedMethodCall) {
            $refactoredNode = $this->refactorInjectedMethodCall(
                $refactoredNode ?? $node,
                $replaceInjectedMethodCall->getOldClass(),
                $replaceInjectedMethodCall->getNewClass(),
                $replaceInjectedMethodCall->getNewVarName(),
                $replaceInjectedMethodCall->getMethodMap(),
            );
        }

        return $refactoredNode;
    }

    /**
     * @param array<string, string> $methodMap
     */
    protected function refactorInjectedMethodCall(
        Node $node,
        string $oldClass,
        string $newClass,
        string $newVarName,
        array $methodMap,
    ): ?Node {
        if (!($node instanceof Class_)) {
            return null;
        }

        $constructor = $node->getMethod(MethodName::CONSTRUCT);
        if (!$constructor instanceof ClassMethod) {
            return null;
        }

        $oldPropertyNames = $this->collectOldPromotedPropertyNames($constructor, $oldClass);
        if ($oldPropertyNames === []) {
            return null;
        }

        $callsToRewrite = $this->collectDeprecatedCalls($node, $oldPropertyNames, array_keys($methodMap));
        if ($callsToRewrite === []) {
            return null;
        }

        $propertyName = $this->findExistingPropertyName($constructor, $newClass);
        if ($propertyName === null) {
            $propertyName = $this->makeUniquePropertyName($constructor, $newVarName);
            $constructor->params[] = $this->buildPromotedParam($propertyName, $newClass);
        }

        foreach ($callsToRewrite as $call) {
            $oldMethodName = $this->getName($call->name);
            if ($oldMethodName === null || !isset($methodMap[$oldMethodName])) {
                continue;
            }
            /** @var PropertyFetch $propertyFetch */
            $propertyFetch = $call->var;
            $propertyFetch->name = new Identifier($propertyName);
            $call->name = new Identifier($methodMap[$oldMethodName]);
        }

        return $node;
    }

    /**
     * @param array<object> $configuration
     *
     * @psalm-suppress MoreSpecificImplementedParamType
     */
    #[Override]
    public function configure(array $configuration): void
    {
        Assert::allIsAOf($configuration, ReplaceInjectedMethodCall::class);
        $this->replaceInjectedMethodCall = $configuration;
    }

    /**
     * @return PhpVersionFeature::PROPERTY_PROMOTION
     */
    #[Override]
    public function provideMinPhpVersion(): int
    {
        return PhpVersionFeature::PROPERTY_PROMOTION;
    }

    /**
     * @return list<string>
     */
    private function collectOldPromotedPropertyNames(ClassMethod $constructor, string $oldClass): array
    {
        $names = [];
        foreach ($constructor->getParams() as $param) {
            if ($param->flags === 0) {
                continue;
            }
            if (!$this->isObjectType($param, new ObjectType($oldClass))) {
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
     * @param list<string> $deprecatedMethods
     *
     * @return list<MethodCall>
     */
    private function collectDeprecatedCalls(Class_ $class, array $propertyNames, array $deprecatedMethods): array
    {
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

    private function findExistingPropertyName(ClassMethod $constructor, string $newClass): ?string
    {
        foreach ($constructor->getParams() as $param) {
            if ($param->flags === 0) {
                continue;
            }
            if (!$this->isObjectType($param, new ObjectType($newClass))) {
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

    private function buildPromotedParam(string $name, string $newClass): Param
    {
        return new Param(
            new Variable($name),
            null,
            new FullyQualified($newClass),
            false,
            false,
            [],
            Modifiers::PRIVATE,
        );
    }
}
