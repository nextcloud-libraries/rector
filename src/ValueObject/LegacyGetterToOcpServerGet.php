<?php

declare(strict_types=1);

/*
 * SPDX-FileCopyrightText: 2024 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-only
 */

namespace Nextcloud\Rector\ValueObject;

use Rector\Validation\RectorAssert;

final class LegacyGetterToOcpServerGet
{
    public function __construct(
        private string $oldMethod,
        private string $newClass,
        private ?string $factoryMethod = null,
    ) {
        RectorAssert::className($oldMethod);
        RectorAssert::className($newClass);
    }

    public function getOldMethod(): string
    {
        return $this->oldMethod;
    }

    public function getNewClass(): string
    {
        return $this->newClass;
    }

    public function getFactoryMethod(): ?string
    {
        return $this->factoryMethod;
    }
}
