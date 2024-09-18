<?php

declare(strict_types=1);

/*
 * SPDX-FileCopyrightText: 2024 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-only
 */

namespace ChristophWurst\Nextcloud\Rector\Rector;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\Name\FullyQualified;
use Rector\Application\Provider\CurrentFileProvider;
use Rector\CodingStyle\Application\UseImportsAdder;
use Rector\CodingStyle\ClassNameImport\ClassNameImportSkipper;
use Rector\PostRector\Collector\UseNodesToAddCollector;
use Rector\Rector\AbstractRector;
use Rector\StaticTypeMapper\PhpParser\FullyQualifiedNodeMapper;
use Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

class OcServerToOcpServerRector extends AbstractRector
{
    public function __construct(
        private UseImportsAdder $useImportsAdder,
        private UseNodesToAddCollector $useNodesToAddCollector,
        private FullyQualifiedNodeMapper $fullyQualifiedNodeMapper,
        private ClassNameImportSkipper $classNameImportSkipper,
        private CurrentFileProvider $currentFileProvider,
    ) {
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
        $methodCallName = $this->getName($node->name);
        if ($methodCallName === null) {
            return null;
        }

        if (
            $methodCallName !== 'get'
            || !($node->var instanceof StaticPropertyFetch)
        ) {
            return null;
        }
        $class = $node->var->class;
        if (
            !($class instanceof FullyQualified)
            || $class->getParts() !== ['OC']
        ) {
            return null;
        }

        $class = $node->args[0]->value->class;
        $serverClass = new FullyQualifiedObjectType('OCP\Server');
        $serverClassNode = new FullyQualified('OCP\Server');
        $file = $this->currentFileProvider->getFile();
        if ($class !== null) {
            $staticType = $this->fullyQualifiedNodeMapper->mapToPHPStan($class);
            if (!$staticType instanceof FullyQualifiedObjectType) {
                return null;
            }
            if (!$this->classNameImportSkipper->shouldSkipNameForFullyQualifiedObjectType($file, $class, $staticType)) {
                $this->useNodesToAddCollector->addUseImport($staticType);
                $node->args[0]->value->class = $staticType->getShortNameNode();
            }
        }
        if (
            !$this->classNameImportSkipper->shouldSkipNameForFullyQualifiedObjectType(
                $file,
                $serverClassNode,
                $serverClass,
            )
        ) {
            $this->useNodesToAddCollector->addUseImport($serverClass);
            $serverClassNode = $serverClass->getShortNameNode();
        }
        $this->useNodesToAddCollector->addUseImport($serverClass);

        return new StaticCall(
            $serverClassNode,
            'get',
            $node->args,
        );
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Change method calls from set* to change*.',
            [
                new CodeSample(
                    '\OC::$server->get(IAppConfig::class);',
                    '\OCP\Server::get(IAppConfig::class);',
                ),
            ],
        );
    }
}
