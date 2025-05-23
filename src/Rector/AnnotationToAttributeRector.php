<?php

declare(strict_types=1);

/*
 * SPDX-FileCopyrightText: 2024 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-only
 */

namespace Nextcloud\Rector\Rector;

use PHPStan\PhpDocParser\Ast\Node as DocNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\GenericTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTextNode;
use PHPStan\Reflection\ReflectionProvider;
use PhpParser\Node;
use PhpParser\Node\AttributeGroup;
use PhpParser\Node\Expr\ArrowFunction;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\Use_;
use Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTagRemover;
use Rector\Comments\NodeDocBlock\DocBlockUpdater;
use Rector\Contract\Rector\ConfigurableRectorInterface;
use Rector\Exception\Configuration\InvalidConfigurationException;
use Rector\Naming\Naming\UseImportsResolver;
use Rector\Php80\NodeAnalyzer\PhpAttributeAnalyzer;
use Rector\Php80\NodeFactory\AttrGroupsFactory;
use Rector\Php80\NodeManipulator\AttributeGroupNamedArgumentManipulator;
use Rector\Php80\Rector\Class_\AttributeValueResolver;
use Rector\Php80\ValueObject\AnnotationToAttribute;
use Rector\Php80\ValueObject\AttributeValueAndDocComment;
use Rector\Php80\ValueObject\DoctrineTagAndAnnotationToAttribute;
use Rector\PhpAttribute\NodeFactory\PhpAttributeGroupFactory;
use Rector\PhpDocParser\PhpDocParser\PhpDocNodeTraverser;
use Rector\Rector\AbstractRector;
use Rector\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use Webmozart\Assert\Assert;

use function array_merge;
use function sprintf;
use function strpos;
use function strtolower;
use function trim;

/** @psalm-suppress PropertyNotSetInConstructor */
final class AnnotationToAttributeRector extends AbstractRector implements
    ConfigurableRectorInterface,
    MinPhpVersionInterface
{
    /**
     * @var AnnotationToAttribute[]
     */
    private array $annotationsToAttributes = [];

    public function __construct(
        private PhpAttributeGroupFactory $phpAttributeGroupFactory,
        private AttrGroupsFactory $attrGroupsFactory,
        private PhpDocTagRemover $phpDocTagRemover,
        private AttributeGroupNamedArgumentManipulator $attributeGroupNamedArgumentManipulator,
        private UseImportsResolver $useImportsResolver,
        private PhpAttributeAnalyzer $phpAttributeAnalyzer,
        private DocBlockUpdater $docBlockUpdater,
        private PhpDocInfoFactory $phpDocInfoFactory,
        private ReflectionProvider $reflectionProvider,
        private AttributeValueResolver $attributeValueResolver,
    ) {
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Change classless annotation to attribute', [new ConfiguredCodeSample(<<<'CODE_SAMPLE'

class Example
{
    /**
     * @OldName("/path", name="action")
     */
    public function action()
    {
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Name\Space\NewName;

class Example
{
    #[NewName(path: '/path', name: 'action')]
    public function action()
    {
    }
}
CODE_SAMPLE
, [new AnnotationToAttribute('OldName', 'Name\Space\NewName')]),
        ]);
    }

    /**
     * @return array<class-string<Node>>
     */
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
    public function refactor(Node $node): ?Node
    {
        if ($this->annotationsToAttributes === []) {
            throw new InvalidConfigurationException(sprintf('The "%s" rule requires configuration.', self::class));
        }
        $phpDocInfo = $this->phpDocInfoFactory->createFromNode($node);
        if (!$phpDocInfo instanceof PhpDocInfo) {
            return null;
        }
        $uses = $this->useImportsResolver->resolveBareUses();
        // 1. Doctrine annotation classes
        $annotationAttributeGroups = $this->processDoctrineAnnotationClasses($phpDocInfo, $uses);
        // 2. bare tags without annotation class, e.g. "@require"
        $genericAttributeGroups = $this->processGenericTags($phpDocInfo);
        $attributeGroups = array_merge($annotationAttributeGroups, $genericAttributeGroups);
        if ($attributeGroups === []) {
            return null;
        }
        // 3. Reprint docblock
        $this->docBlockUpdater->updateRefactoredNodeWithPhpDocInfo($node);
        $this->attributeGroupNamedArgumentManipulator->decorate($attributeGroups);
        $node->attrGroups = array_merge($node->attrGroups, $attributeGroups);

        return $node;
    }

    /**
     * @param array<object> $configuration
     *
     * @psalm-suppress MoreSpecificImplementedParamType
     */
    public function configure(array $configuration): void
    {
        Assert::allIsAOf($configuration, AnnotationToAttribute::class);
        $this->annotationsToAttributes = $configuration;
    }

    /**
     * @return PhpVersionFeature::ATTRIBUTES
     */
    public function provideMinPhpVersion(): int
    {
        return PhpVersionFeature::ATTRIBUTES;
    }

    /**
     * @return AttributeGroup[]
     */
    private function processGenericTags(PhpDocInfo $phpDocInfo): array
    {
        $attributeGroups = [];
        $phpDocNodeTraverser = new PhpDocNodeTraverser();
        $phpDocNodeTraverser->traverseWithCallable(
            $phpDocInfo->getPhpDocNode(),
            '',
            function (DocNode $docNode) use (&$attributeGroups) {
                if (!$docNode instanceof PhpDocTagNode) {
                    return null;
                }
                if (
                    !$docNode->value instanceof GenericTagValueNode
                    && !$docNode->value instanceof DoctrineAnnotationTagValueNode
                ) {
                    return null;
                }
                $tag = trim($docNode->name, '@');
                // not a basic one
                if (strpos($tag, '\\') !== \false) {
                    return null;
                }
                foreach ($this->annotationsToAttributes as $annotationToAttribute) {
                    $desiredTag = $annotationToAttribute->getTag();
                    if (strtolower($desiredTag) !== strtolower($tag)) {
                        continue;
                    }
                    // make sure the attribute class really exists to avoid error on early upgrade
                    if (!$this->reflectionProvider->hasClass($annotationToAttribute->getAttributeClass())) {
                        continue;
                    }
                    $attributeValueAndDocComment = $this->attributeValueResolver->resolve(
                        $annotationToAttribute,
                        $docNode,
                    );
                    $attributeGroups[] = $this->phpAttributeGroupFactory->createFromSimpleTag(
                        $annotationToAttribute,
                        $attributeValueAndDocComment instanceof AttributeValueAndDocComment ?
                            $attributeValueAndDocComment->attributeValue : null,
                    );
                    // keep partial original comment, if useful
                    if (
                        $attributeValueAndDocComment instanceof AttributeValueAndDocComment
                        && $attributeValueAndDocComment->docComment
                    ) {
                        return new PhpDocTextNode($attributeValueAndDocComment->docComment);
                    }

                    return PhpDocNodeTraverser::NODE_REMOVE;
                }

                return null;
            },
        );

        return $attributeGroups;
    }

    /**
     * @param Use_[] $uses
     *
     * @return AttributeGroup[]
     */
    private function processDoctrineAnnotationClasses(PhpDocInfo $phpDocInfo, array $uses): array
    {
        if ($phpDocInfo->getPhpDocNode()->children === []) {
            return [];
        }
        $doctrineTagAndAnnotationToAttributes = [];
        $doctrineTagValueNodes = [];
        foreach ($phpDocInfo->getPhpDocNode()->children as $phpDocChildNode) {
            if (!$phpDocChildNode instanceof PhpDocTagNode) {
                continue;
            }
            if (!$phpDocChildNode->value instanceof DoctrineAnnotationTagValueNode) {
                continue;
            }
            $doctrineTagValueNode = $phpDocChildNode->value;
            $annotationToAttribute = $this->matchAnnotationToAttribute($doctrineTagValueNode);
            if (!$annotationToAttribute instanceof AnnotationToAttribute) {
                continue;
            }
            // make sure the attribute class really exists to avoid error on early upgrade
            if (!$this->reflectionProvider->hasClass($annotationToAttribute->getAttributeClass())) {
                continue;
            }
            $doctrineTagAndAnnotationToAttributes[] = new DoctrineTagAndAnnotationToAttribute(
                $doctrineTagValueNode,
                $annotationToAttribute,
            );
            $doctrineTagValueNodes[] = $doctrineTagValueNode;
        }
        $attributeGroups = $this->attrGroupsFactory->create($doctrineTagAndAnnotationToAttributes, $uses);
        if ($this->phpAttributeAnalyzer->hasRemoveArrayState($attributeGroups)) {
            return [];
        }
        foreach ($doctrineTagValueNodes as $doctrineTagValueNode) {
            $this->phpDocTagRemover->removeTagValueFromNode($phpDocInfo, $doctrineTagValueNode);
        }

        return $attributeGroups;
    }

    private function matchAnnotationToAttribute(
        DoctrineAnnotationTagValueNode $doctrineAnnotationTagValueNode,
    ): ?AnnotationToAttribute {
        foreach ($this->annotationsToAttributes as $annotationToAttribute) {
            if (!$doctrineAnnotationTagValueNode->hasClassName($annotationToAttribute->getTag())) {
                continue;
            }

            return $annotationToAttribute;
        }

        return null;
    }
}
