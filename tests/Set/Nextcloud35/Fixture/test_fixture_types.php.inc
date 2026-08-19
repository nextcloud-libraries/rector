<?php

/*
 * SPDX-FileCopyrightText: 2026 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace Nextcloud\Rector\Test\Set\Nextcloud35\Fixture;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\DB\Types;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

class SomeTypesMigration extends SimpleMigrationStep
{
    public function changeSchema(IOutput $output, Closure $schemaClosure, array $options): ?ISchemaWrapper
    {
        $schema = $schemaClosure();

        $table = $schema->createTable('my_table');
        $table->addColumn('id', Types::INTEGER, ['autoincrement' => true, 'notnull' => true]);
        $table->addColumn('name', Types::STRING, ['length' => 64]);
        $table->addColumn('created', Types::DATETIME_IMMUTABLE);

        return $schema;
    }
}

?>
-----
<?php

/*
 * SPDX-FileCopyrightText: 2026 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace Nextcloud\Rector\Test\Set\Nextcloud35\Fixture;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\DB\Types;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

class SomeTypesMigration extends SimpleMigrationStep
{
    public function changeSchema(IOutput $output, Closure $schemaClosure, array $options): ?ISchemaWrapper
    {
        $schema = $schemaClosure();

        $table = $schema->createTable('my_table');
        $table->addColumn('id', \OCP\DB\Schema\ColumnType::Integer, ['autoincrement' => true, 'notnull' => true]);
        $table->addColumn('name', \OCP\DB\Schema\ColumnType::String, ['length' => 64]);
        $table->addColumn('created', \OCP\DB\Schema\ColumnType::DatetimeImmutable);

        return $schema;
    }
}

?>
