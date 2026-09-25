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

use function array_combine;
use function array_keys;
use function array_map;
use function count;
use function in_array;
use function iterator_to_array;

/** @psalm-suppress PropertyNotSetInConstructor */
final class TestCaseAutoMockRefactorRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Migrate test cases to new createInstanceWithMock method',
            [
                new ConfiguredCodeSample(
                    <<<'CODE_SAMPLE'
<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2017 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Comments\Tests\Unit\Collaboration;

use OCA\Comments\Collaboration\CommentersSorter;
use OCP\Comments\IComment;
use OCP\Comments\ICommentsManager;
use PHPUnit\Framework\MockObject\MockObject;
use Test\TestCase;

class CommentersSorterTest extends TestCase {
	protected ICommentsManager&MockObject $commentsManager;
	protected CommentersSorter $sorter;

	#[\Override]
	protected function setUp(): void {
		parent::setUp();

		$this->commentsManager = $this->createMock(ICommentsManager::class);

		$this->sorter = new CommentersSorter($this->commentsManager);
	}

	#[\PHPUnit\Framework\Attributes\DataProvider(methodName: 'sortDataProvider')]
	public function testSort($data): void {
		$this->commentsManager->expects($this->once())
			->method('getForObject')
			->willReturn([]);

		$workArray = $data['input'];
		$this->sorter->sort($workArray, ['itemType' => 'files', 'itemId' => '24']);

		$this->assertEquals($data['expected'], $workArray);
	}
}

CODE_SAMPLE,
                    <<<'CODE_SAMPLE'
<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2017 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Comments\Tests\Unit\Collaboration;

use OCA\Comments\Collaboration\CommentersSorter;
use OCP\Comments\IComment;
use OCP\Comments\ICommentsManager;
use PHPUnit\Framework\MockObject\MockObject;
use Test\TestCase;

class CommentersSorterTest extends TestCase {
	protected ICommentsManager&MockObject $commentsManager;
	protected CommentersSorter $sorter;

	#[\Override]
	protected function setUp(): void {
		parent::setUp();

		$this->commentsManager = $this->createMock(ICommentsManager::class);

		$this->sorter = new CommentersSorter($this->commentsManager);
	}

	#[\PHPUnit\Framework\Attributes\DataProvider(methodName: 'sortDataProvider')]
	public function testSort($data): void {
		$this->commentsManager->expects($this->once())
			->method('getForObject')
			->willReturn([]);

		$workArray = $data['input'];
		$this->sorter->sort($workArray, ['itemType' => 'files', 'itemId' => '24']);

		$this->assertEquals($data['expected'], $workArray);
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
        $mockAssignments = array_combine(
            array_map(
                fn (Expression $node): string => $node->expr->var->name->name,
                $mockAssignments,
            ),
            $mockAssignments,
        );
        $this->replaceMethodCallsToMocks(
            $node,
            $mockAssignments,
        );

        return $node;
    }

    /**
     * @psalm-suppress MoreSpecificReturnType
     * @psalm-suppress LessSpecificReturnStatement
     */
    private function collectConstructorCall(ClassMethod $setupMethod): ?Node\Expr\New_
    {
        $stmts = $setupMethod->getStmts();
        $nodeFinder = new NodeFinder();
        if ($stmts === null) {
            return null;
        }

        // Find first class that has name $name
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
     *
     * @psalm-suppress LessSpecificReturnStatement
     */
    private function collectMockAssignments(ClassMethod $setupMethod, array $constructorArgs): array
    {
        $stmts = $setupMethod->getStmts();
        $nodeFinder = new NodeFinder();
        if ($stmts === null) {
            return [];
        }

        return $nodeFinder->find($stmts, fn (Node $node) => $node instanceof Expression
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
    }

    private function replaceStatement(ClassMethod $node, Node $search, Node $replace): void
    {
        $traverser = new NodeTraverser();
        $traverser->addVisitor(new class ($search, $replace) extends NodeVisitorAbstract {
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

        if ($node->stmts !== null) {
            $node->stmts = $traverser->traverse($node->stmts);
        }
    }

    /**
     * @param list<Node> $toRemove
     */
    private function removeStatements(ClassMethod $node, array $toRemove): void
    {
        $traverser = new NodeTraverser();
        $traverser->addVisitor(new class ($toRemove) extends NodeVisitorAbstract {
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
                    return NodeVisitor::REMOVE_NODE;
                }

                return null;
            }
        });

        $node->stmts = $traverser->traverse($node->stmts);
    }

    /**
     * @param list<string> $toRemove
     */
    private function removePropertiesByName(Class_ $node, array $toRemove): void
    {
        $traverser = new NodeTraverser();
        $traverser->addVisitor(new class ($toRemove) extends NodeVisitorAbstract {
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
                    return NodeVisitor::REMOVE_NODE;
                }

                return null;
            }
        });

        $node->stmts = $traverser->traverse($node->stmts);
    }

    /**
     * @param array<string, Expression> $mocks
     */
    private function replaceMethodCallsToMocks(Class_ $node, array $mocks): void
    {
        $traverser = new NodeTraverser();
        $traverser->addVisitor(new class ($mocks) extends NodeVisitorAbstract {
            /**
            * @param array<string, Expression> $mocks
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
                    && $this->mocks[$node->name->name]->expr instanceof Assign
                    && $this->mocks[$node->name->name]->expr->expr instanceof MethodCall
                    && $this->mocks[$node->name->name]->expr->expr->args[0] instanceof Arg
                    && ($this->mocks[$node->name->name]->expr->expr->args[0]->value instanceof ClassConstFetch)
                ) {
                    return new ArrayDimFetch(
                        new PropertyFetch(
                            new Variable('this'),
                            'mocks',
                        ),
                        // We reuse the ClassConstFetch from the createMock args
                        $this->mocks[$node->name->name]->expr->expr->args[0]->value,
                    );
                }

                return null;
            }
        });

        $node->stmts = $traverser->traverse($node->stmts);
    }
}
