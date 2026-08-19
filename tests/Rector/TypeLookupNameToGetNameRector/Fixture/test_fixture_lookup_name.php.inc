<?php

/*
 * SPDX-FileCopyrightText: 2026 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace Nextcloud\Rector\Test\Rector\TypeLookupNameToGetNameRector\Fixture;

use Doctrine\DBAL\Types\Type;
use OCP\DB\Schema\ColumnType;
use OCP\DB\Types;

class SomeClass
{
    public function run($column, $sourceColumn): void
    {
        $typeName = Type::lookupName($column->getType());

        // Comparison against the legacy OCP\DB\Types constant.
        if (Type::lookupName($column->getType()) === Types::BOOLEAN) {
            return;
        }

        // Comparison against a plain string literal, constant on the left.
        if ('string' !== Type::lookupName($sourceColumn->getType())) {
            return;
        }

        // Comparison against the already migrated ColumnType enum.
        if (Type::lookupName($column->getType()) !== ColumnType::Text) {
            return;
        }

        // Unrecognized comparison value, must fall back to the plain ->getName() call.
        if (Type::lookupName($column->getType()) === 'notatype') {
            return;
        }
    }
}

?>
-----
<?php

/*
 * SPDX-FileCopyrightText: 2026 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace Nextcloud\Rector\Test\Rector\TypeLookupNameToGetNameRector\Fixture;

use Doctrine\DBAL\Types\Type;
use OCP\DB\Schema\ColumnType;
use OCP\DB\Types;

class SomeClass
{
    public function run($column, $sourceColumn): void
    {
        $typeName = $column->getType()->getName();

        // Comparison against the legacy OCP\DB\Types constant.
        if ($column->getType() === \OCP\DB\Schema\ColumnType::Boolean) {
            return;
        }

        // Comparison against a plain string literal, constant on the left.
        if (\OCP\DB\Schema\ColumnType::String !== $sourceColumn->getType()) {
            return;
        }

        // Comparison against the already migrated ColumnType enum.
        if ($column->getType() !== \OCP\DB\Schema\ColumnType::Text) {
            return;
        }

        // Unrecognized comparison value, must fall back to the plain ->getName() call.
        if ($column->getType()->getName() === 'notatype') {
            return;
        }
    }
}

?>
