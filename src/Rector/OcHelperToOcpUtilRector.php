<?php

declare(strict_types=1);

/*
 * SPDX-FileCopyrightText: 2026 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-only
 */

namespace Nextcloud\Rector\Rector;

use Override;
use PhpParser\Node;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Name\FullyQualified;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

final class OcHelperToOcpUtilRector extends AbstractRector
{
    private const METHODS = [
        'computerFileSize',
        'humanFileSize',
    ];

    /**
     * @return array<class-string<Node>>
     */
    #[Override]
    public function getNodeTypes(): array
    {
        return [StaticCall::class];
    }

    #[Override]
    public function refactor(Node $node): ?Node
    {
        if (!($node instanceof StaticCall)) {
            return null;
        }
        if (!$this->isName($node->class, 'OC_Helper')) {
            return null;
        }
        if (!$this->isNames($node->name, self::METHODS)) {
            return null;
        }

        $node->class = new FullyQualified('OCP\Util');

        return $node;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Change static calls of OC_Helper::computerFileSize and OC_Helper::humanFileSize to OCP\Util.',
            [
                new CodeSample(
                    '\OC_Helper::humanFileSize(1223);',
                    '\OCP\Util::humanFileSize(1223);',
                ),
            ],
        );
    }
}
