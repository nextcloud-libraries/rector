<?php

declare(strict_types=1);

/*
 * SPDX-FileCopyrightText: 2024 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace Nextcloud\Rector\ValueObject;

use Rector\Validation\RectorAssert;

final class RenameParameter
{
    public function __construct(
        private string $oldName,
        private string $newName,
    ) {
        RectorAssert::propertyName($oldName);
        RectorAssert::propertyName($newName);
    }

    public function getOldName(): string
    {
        return $this->oldName;
    }

    public function getNewName(): string
    {
        return $this->newName;
    }
}
