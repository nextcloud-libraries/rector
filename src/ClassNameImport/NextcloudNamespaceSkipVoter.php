<?php

declare(strict_types=1);

/*
 * SPDX-FileCopyrightText: 2024 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-only
 */

namespace Nextcloud\Rector\ClassNameImport;

use PhpParser\Node;
use Rector\CodingStyle\Contract\ClassNameImport\ClassNameImportSkipVoterInterface;
use Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;
use Rector\ValueObject\Application\File;

use function in_array;
use function str_starts_with;

/**
 * This allows to import only classes from Nextcloud namespaces (OC/OCP/OCA), except for common names (Plugin/Backend/…)
 *
 * To use it:
 * $config = RectorConfig::configure()[…]->withImportNames(importShortClasses:false);
 * $config->registerService(NextcloudNamespaceSkipVoter::class, tag:ClassNameImportSkipVoterInterface::class);
 */
class NextcloudNamespaceSkipVoter implements ClassNameImportSkipVoterInterface
{
    /**
     * @var list<string>
     */
    protected array $namespacePrefixes = [
        'OC',
        'OCA',
        'OCP',
    ];
    /**
     * @var list<string>
     */
    protected array $skippedClassNames = [
        'Backend',
        'Connection',
        'Exception',
        'IManager',
        'IProvider',
        'Manager',
        'Plugin',
        'Provider',
    ];

    public function shouldSkip(File $file, FullyQualifiedObjectType $fullyQualifiedObjectType, Node $node): bool
    {
        if (in_array($fullyQualifiedObjectType->getShortName(), $this->skippedClassNames)) {
            // Skip common class names to avoid confusion
            return true;
        }
        foreach ($this->namespacePrefixes as $prefix) {
            if (str_starts_with($fullyQualifiedObjectType->getClassName(), $prefix . '\\')) {
                // Import Nextcloud namespaces
                return false;
            }
        }

        // Skip everything else
        return true;
    }
}
