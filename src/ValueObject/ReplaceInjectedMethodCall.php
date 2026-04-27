<?php

declare(strict_types=1);

/*
 * SPDX-FileCopyrightText: 2026 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace Nextcloud\Rector\ValueObject;

use InvalidArgumentException;
use Rector\Validation\RectorAssert;

final class ReplaceInjectedMethodCall
{
    /**
     * @param array<string, string> $methodMap
     */
    public function __construct(
        private string $oldClass,
        private string $newClass,
        private string $newVarName,
        private array $methodMap,
    ) {
        RectorAssert::className($oldClass);
        RectorAssert::className($newClass);
        RectorAssert::propertyName($newVarName);

        if ($methodMap === []) {
            throw new InvalidArgumentException('"methodMap" is not a valid dictionary');
        }
        foreach ($methodMap as $oldMethod => $newMethod) {
            RectorAssert::propertyName($oldMethod);
            RectorAssert::propertyName($newMethod);
        }
    }

    public function getOldClass(): string
    {
        return $this->oldClass;
    }

    public function getNewClass(): string
    {
        return $this->newClass;
    }

    public function getNewVarName(): string
    {
        return $this->newVarName;
    }

    /**
     * @return array<string, string>
     */
    public function getMethodMap(): array
    {
        return $this->methodMap;
    }
}
