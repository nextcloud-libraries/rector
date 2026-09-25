<?php

declare(strict_types=1);

/*
 * SPDX-FileCopyrightText: 2026 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace Nextcloud\Rector\Rector;

use Generator;
use Override;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Property;
use PhpParser\NodeFinder;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor;
use PhpParser\NodeVisitorAbstract;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

use function array_keys;
use function array_splice;
use function count;
use function in_array;
use function iterator_to_array;

/** @psalm-suppress PropertyNotSetInConstructor */
final class TestCaseAutoMockRefactorRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Migrate test cases to new createInstanceWithMocks method',
            [
                new ConfiguredCodeSample(
                    <<<'CODE_SAMPLE'
class CommentersSorterTest extends TestCase {
	protected ICommentsManager&MockObject $commentsManager;
	protected CommentersSorter $sorter;

	#[\Override]
	protected function setUp(): void {
		parent::setUp();

		$this->commentsManager = $this->createMock(ICommentsManager::class);

		$this->sorter = new CommentersSorter($this->commentsManager);
	}

	public function testSort(): void {
		$this->commentsManager->expects($this->once())
			->method('getForObject')
			->willReturn([]);

		$this->assertEquals(true, $this->sorter->testedMethod());
	}
}
CODE_SAMPLE,
                    <<<'CODE_SAMPLE'
class CommentersSorterTest extends TestCase {
	protected CommentersSorter $sorter;

	#[\Override]
	protected function setUp(): void {
		parent::setUp();

		$this->sorter = $this->createInstanceWithMocks(CommentersSorter::class);
	}

	public function testSort(): void {
		$this->mocks[ICommentsManager::class]->expects($this->once())
			->method('getForObject')
			->willReturn([]);

		$this->assertEquals(true, $this->sorter->testedMethod());
	}
}
CODE_SAMPLE
                    ,
                    [
                        new TestCaseAutoMockRefactorRector(),
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
        return [Class_::class];
    }

    /**
     * @param Class_ $node
     *
     * @psalm-suppress MoreSpecificImplementedParamType
     */
    #[Override]
    public function refactor(Node $node): ?Node
    {
        if (!($node instanceof Class_)) {
            return null;
        }

        if (!isset($node->extends) || ((string) $node->extends !== 'Test\TestCase')) {
            // Not extending Nextcloud TestCase, skip
            return null;
        }

        $setupMethod = $node->getMethod('setUp');
        if (!$setupMethod instanceof ClassMethod) {
            return null;
        }

        $constructorCall = $this->collectConstructorCall($setupMethod);
        if ($constructorCall === null || !($constructorCall->class instanceof Node\Name)) {
            return null;
        }

        if ($constructorCall->class->getLast() . 'Test' !== (string) $node->name) {
            // The test class name does not match the tested class
            return null;
        }

        $mockedServices = iterator_to_array($this->collectMockedServices($constructorCall));
        if ($mockedServices === []) {
            return null;
        }

        $mockAssignments = $this->collectMockAssignments($setupMethod, $mockedServices);
        if ($mockAssignments === []) {
            return null;
        }

        if (count($mockAssignments) !== count($constructorCall->args)) {
            // For now we skip the complicated cases
            return null;
        }

        $this->replaceStatement(
            $setupMethod,
            $constructorCall,
            new MethodCall(
                new Node\Expr\Variable('this'),
                'createInstanceWithMocks',
                [
                    new Arg(new ClassConstFetch($constructorCall->class, 'class')),
                ],
            ),
        );
        $this->removeStatements(
            $setupMethod,
            $mockAssignments,
        );
        $this->removePropertiesByName(
            $node,
            array_keys($mockedServices),
        );
        $constFetchMap = [];
        foreach ($mockAssignments as $mockAssignment) {
            if (
                $mockAssignment->expr instanceof Assign
                && $mockAssignment->expr->var instanceof PropertyFetch
                && $mockAssignment->expr->var->name instanceof Node\Identifier
                && $mockAssignment->expr->expr instanceof MethodCall
                && $mockAssignment->expr->expr->args[0] instanceof Arg
                && $mockAssignment->expr->expr->args[0]->value instanceof ClassConstFetch
            ) {
                $constFetchMap[$mockAssignment->expr->var->name->name] = $mockAssignment->expr->expr->args[0]->value;
            }
        }
        $this->replaceMethodCallsToMocks(
            $node,
            $constFetchMap,
        );
        $this->reorderSetupStatements($setupMethod);

        return $node;
    }

    private function collectConstructorCall(ClassMethod $setupMethod): ?Node\Expr\New_
    {
        $stmts = $setupMethod->getStmts();
        $nodeFinder = new NodeFinder();
        if ($stmts === null) {
            return null;
        }

        /**
         * @var ?Node\Expr\New_
         */
        return $nodeFinder->findFirst($stmts, fn (Node $node) => $node instanceof Node\Expr\New_);
    }

    /**
     * @return Generator<string, PropertyFetch>
     */
    private function collectMockedServices(Node\Expr\New_ $constructorCall): Generator
    {
        foreach ($constructorCall->args as $arg) {
            if (!($arg instanceof Arg)) {
                continue;
            }
            if (!($arg->value instanceof PropertyFetch)) {
                continue;
            }
            if (!($arg->value->var instanceof Variable)) {
                continue;
            }
            if (!($arg->value->var->name === 'this')) {
                continue;
            }
            if (!($arg->value->name instanceof Node\Identifier)) {
                continue;
            }
            yield $arg->value->name->name => $arg->value;
        }
    }

    /**
     * @param array<string, PropertyFetch> $constructorArgs
     *
     * @return list<Expression>
     */
    private function collectMockAssignments(ClassMethod $setupMethod, array $constructorArgs): array
    {
        $stmts = $setupMethod->getStmts();
        $nodeFinder = new NodeFinder();
        if ($stmts === null) {
            return [];
        }

        /**
         * @var list<Expression> $exprs
         */
        $exprs = $nodeFinder->find($stmts, fn (Node $node) => $node instanceof Expression
                && $node->expr instanceof Assign
                && $node->expr->var instanceof PropertyFetch
                && $node->expr->var->var instanceof Variable
                && $node->expr->var->var->name === 'this'
                && $node->expr->expr instanceof MethodCall
                && $node->expr->expr->var instanceof Variable
                && $node->expr->expr->var->name === 'this'
                && $node->expr->expr->name instanceof Node\Identifier
                && $node->expr->expr->name->name === 'createMock'
                && $node->expr->var->name instanceof Node\Identifier
                && isset($constructorArgs[$node->expr->var->name->name]));

        return $exprs;
    }

    private function replaceStatement(ClassMethod $node, Node $search, Node $replace): void
    {
        $this->traverseWithVisitor($node, new class ($search, $replace) extends NodeVisitorAbstract {
            public function __construct(
                private Node $search,
                private Node $replace,
            ) {
            }

            public function leaveNode(Node $node): ?Node
            {
                if ($node === $this->search) {
                    return $this->replace;
                }

                return null;
            }
        });
    }

    /**
     * @param list<Node> $toRemove
     */
    private function removeStatements(ClassMethod $node, array $toRemove): void
    {
        $this->traverseWithVisitor($node, new class ($toRemove) extends NodeVisitorAbstract {
            /**
            * @param list<Node> $toRemove
            */
            public function __construct(
                private array $toRemove,
            ) {
            }

            public function leaveNode(Node $node): ?int
            {
                if (in_array($node, $this->toRemove, true)) {
                    return NodeTraverser::REMOVE_NODE;
                }

                return null;
            }
        });
    }

    /**
     * @param list<string> $toRemove
     */
    private function removePropertiesByName(Class_ $node, array $toRemove): void
    {
        $this->traverseWithVisitor($node, new class ($toRemove) extends NodeVisitorAbstract {
            /**
            * @param list<string> $toRemove
            */
            public function __construct(
                private array $toRemove,
            ) {
            }

            public function leaveNode(Node $node): ?int
            {
                if ($node instanceof Property && in_array($node->props[0]->name->name, $this->toRemove, true)) {
                    return NodeTraverser::REMOVE_NODE;
                }

                return null;
            }
        });
    }

    /**
     * @param array<string, ClassConstFetch> $mocks
     */
    private function replaceMethodCallsToMocks(Class_ $node, array $mocks): void
    {
        $this->traverseWithVisitor($node, new class ($mocks) extends NodeVisitorAbstract {
            /**
            * @param array<string, ClassConstFetch> $mocks
            */
            public function __construct(
                private array $mocks,
            ) {
            }

            public function leaveNode(Node $node): ?Node
            {
                if (
                    $node instanceof PropertyFetch
                    && $node->var instanceof Variable
                    && $node->var->name === 'this'
                    && $node->name instanceof Node\Identifier
                    && isset($this->mocks[$node->name->name])
                ) {
                    return new ArrayDimFetch(
                        new PropertyFetch(
                            new Variable('this'),
                            'mocks',
                        ),
                        // We reuse the ClassConstFetch from the createMock args
                        $this->mocks[$node->name->name],
                    );
                }

                return null;
            }
        });
    }

    private function traverseWithVisitor(Class_ | ClassMethod $node, NodeVisitor $visitor): void
    {
        if ($node->stmts === null) {
            return;
        }
        $traverser = new NodeTraverser();
        $traverser->addVisitor($visitor);

        /** @var list<Node\Stmt> $newStmts */
        $newStmts = $traverser->traverse($node->stmts);
        $node->stmts = $newStmts;
    }

    private function reorderSetupStatements(ClassMethod $setupMethod): void
    {
        /**
         * @var list<Node\Stmt>|null $stmts
         */
        $stmts = $setupMethod->getStmts();
        $nodeFinder = new NodeFinder();
        if ($stmts === null) {
            return;
        }

        $firstMockUse = -1;
        $firstCreateUse = -1;
        foreach ($stmts as $key => $stmt) {
            if ($firstMockUse < 0) {
                // Search in subnodes
                $mockArrayUse = $nodeFinder->findFirst(
                    $stmt,
                    fn (Node $node) => $node instanceof ArrayDimFetch
                    && $node->var instanceof PropertyFetch
                    && $node->var->var instanceof Variable
                    && $node->var->var->name === 'this'
                    && $node->var->name instanceof Node\Identifier
                    && $node->var->name->name === 'mocks',
                );
                if ($mockArrayUse !== null) {
                    $firstMockUse = $key;
                }
            }
            if ($firstCreateUse < 0) {
                // Search in subnodes
                $createUse = $nodeFinder->findFirst(
                    $stmt,
                    fn (Node $node) => $node instanceof MethodCall
                        && $node->name instanceof Node\Identifier
                        && $node->name->name === 'createInstanceWithMocks'
                        && $node->var instanceof Variable
                        && $node->var->name === 'this',
                );
                if ($createUse !== null) {
                    $firstCreateUse = $key;
                }
            }
        }
        if ($firstCreateUse < 0 || $firstMockUse < 0 || $firstCreateUse < $firstMockUse) {
            // No reorder needed
            return;
        }
        // Remove create
        $create = array_splice($stmts, $firstCreateUse, 1);
        array_splice($stmts, $firstMockUse, 0, $create);
        $setupMethod->stmts = $stmts;
    }
}
