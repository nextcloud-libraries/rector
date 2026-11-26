<?php

declare(strict_types=1);

/*
 * SPDX-FileCopyrightText: 2026 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace Nextcloud\Rector\Rector;

use Override;
use PHPStan\PhpDocParser\Ast\Node as DocNode;
use PHPStan\PhpDocParser\Ast\NodeTraverser;
use PHPStan\PhpDocParser\Ast\Type\FullyQualifiedNameNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\CodingStyle\Node\NameImporter;
use Rector\Comments\NodeDocBlock\DocBlockUpdater;
use Rector\Naming\Naming\UseImportsResolver;
use Rector\NodeTypeResolver\PhpDocNodeVisitor\NameImportingPhpDocNodeVisitor;
use Rector\PhpDocParser\PhpDocParser\PhpDocNodeVisitor\CallablePhpDocNodeVisitor;
use Rector\PostRector\Guard\AddUseStatementGuard;
use Rector\Rector\AbstractRector;
use Rector\StaticTypeMapper\PhpDocParser\IdentifierPhpDocTypeMapper;
use Rector\StaticTypeMapper\ValueObject\Type\AliasedObjectType;
use Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;
use Rector\StaticTypeMapper\ValueObject\Type\ShortenedObjectType;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

use function in_array;
use function ltrim;
use function str_contains;
use function str_starts_with;
use function strrpos;
use function substr;

/**
 * Imports class names from the Nextcloud namespaces (OC, OCA, OCP) in code and docblocks,
 * except for short names that are too generic to be readable without their namespace.
 *
 * Use it instead of `withImportNames()`, which imports names from all namespaces.
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
final class ImportNextcloudNamesRector extends AbstractRector
{
    private const NAMESPACE_PREFIXES = [
        'OC\\',
        'OCA\\',
        'OCP\\',
    ];

    private const SKIPPED_SHORT_NAMES = [
        'Backend',
        'Connection',
        'Exception',
        'IManager',
        'IProvider',
        'Manager',
        'Plugin',
        'Provider',
    ];

    public function __construct(
        private NameImporter $nameImporter,
        private UseImportsResolver $useImportsResolver,
        private AddUseStatementGuard $addUseStatementGuard,
        private PhpDocInfoFactory $phpDocInfoFactory,
        private NameImportingPhpDocNodeVisitor $nameImportingPhpDocNodeVisitor,
        private IdentifierPhpDocTypeMapper $identifierPhpDocTypeMapper,
        private DocBlockUpdater $docBlockUpdater,
    ) {
    }

    /**
     * @return array<class-string<Node>>
     */
    #[Override]
    public function getNodeTypes(): array
    {
        return [FullyQualified::class, Stmt::class, Param::class];
    }

    #[Override]
    public function refactor(Node $node): ?Node
    {
        $file = $this->getFile();
        if (!$this->addUseStatementGuard->shouldTraverse($file->getNewStmts(), $file)) {
            return null;
        }

        if ($node instanceof FullyQualified) {
            return $this->importName($node);
        }

        if ($node instanceof Stmt || $node instanceof Param) {
            return $this->importDocBlockNames($node);
        }

        return null;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Import class names from the Nextcloud namespaces, except for generic short names like IManager',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
                        namespace OCA\MyApp;

                        class SomeClass
                        {
                            public function __construct(
                                private \OCP\IUserManager $userManager,
                                private \OCP\Share\IManager $shareManager,
                                private \Psr\Log\LoggerInterface $logger,
                            ) {
                            }
                        }
                        CODE_SAMPLE,
                    <<<'CODE_SAMPLE'
                        namespace OCA\MyApp;

                        use OCP\IUserManager;

                        class SomeClass
                        {
                            public function __construct(
                                private IUserManager $userManager,
                                private \OCP\Share\IManager $shareManager,
                                private \Psr\Log\LoggerInterface $logger,
                            ) {
                            }
                        }
                        CODE_SAMPLE,
                ),
            ],
        );
    }

    private function importName(FullyQualified $fullyQualified): ?Name
    {
        if (!$this->isImportable($fullyQualified->toString())) {
            return null;
        }

        return $this->nameImporter->importName($fullyQualified, $this->getFile(), $this->useImportsResolver->resolve());
    }

    private function importDocBlockNames(Stmt | Param $node): ?Node
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNode($node);
        if (!$phpDocInfo instanceof PhpDocInfo) {
            return null;
        }

        $this->nameImportingPhpDocNodeVisitor->setCurrentNode($node);
        $phpDocNodeVisitor = new CallablePhpDocNodeVisitor(
            function (DocNode $docNode) use ($node): ?DocNode {
                if (!$this->isImportableDocName($docNode, $node)) {
                    return null;
                }

                return $this->nameImportingPhpDocNodeVisitor->enterNode($docNode);
            },
        );
        $phpDocNodeTraverser = new NodeTraverser([$phpDocNodeVisitor]);
        $phpDocNodeTraverser->traverse([$phpDocInfo->getPhpDocNode()]);

        if (!$this->nameImportingPhpDocNodeVisitor->hasChanged()) {
            return null;
        }

        $this->docBlockUpdater->updateRefactoredNodeWithPhpDocInfo($node);

        return $node;
    }

    private function isImportableDocName(DocNode $docNode, Stmt | Param $node): bool
    {
        if ($docNode instanceof FullyQualifiedNameNode) {
            return $this->isImportable($docNode->name);
        }

        if (!$docNode instanceof IdentifierTypeNode) {
            return false;
        }
        if (!str_contains($docNode->name, '\\')) {
            return false;
        }

        $type = $this->identifierPhpDocTypeMapper->mapIdentifierTypeNode($docNode, $node);
        if ($type instanceof ShortenedObjectType || $type instanceof AliasedObjectType) {
            return $this->isImportable($type->getFullyQualifiedName());
        }
        if ($type instanceof FullyQualifiedObjectType) {
            return $this->isImportable($type->getClassName());
        }

        return false;
    }

    private function isImportable(string $className): bool
    {
        $className = ltrim($className, '\\');
        $separatorPosition = strrpos($className, '\\');
        $shortName = $separatorPosition === false ? $className : substr($className, $separatorPosition + 1);
        if (in_array($shortName, self::SKIPPED_SHORT_NAMES, true)) {
            return false;
        }

        foreach (self::NAMESPACE_PREFIXES as $prefix) {
            if (str_starts_with($className, $prefix)) {
                return true;
            }
        }

        return false;
    }
}
