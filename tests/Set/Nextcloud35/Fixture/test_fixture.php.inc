<?php

/*
 * SPDX-FileCopyrightText: 2026 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace Nextcloud\Rector\Test\Set\Nextcloud35\Fixture;

use Closure;
use Doctrine\DBAL\Schema\SchemaException;
use Doctrine\DBAL\Schema\Table;
use OCP\DB\ISchemaWrapper;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

class SomeMigration extends SimpleMigrationStep
{
    /**
     * @throws SchemaException
     */
    public function changeSchema(IOutput $output, Closure $schemaClosure, array $options): ?ISchemaWrapper
    {
        $schema = $schemaClosure();

        $table = $schema->getTable('my_table');
        $this->doSomething($table);

        return $schema;
    }

    private function doSomething(Table $table): void
    {
        $table->addColumn('col', 'integer');
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
use Doctrine\DBAL\Schema\SchemaException;
use Doctrine\DBAL\Schema\Table;
use OCP\DB\ISchemaWrapper;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

class SomeMigration extends SimpleMigrationStep
{
    /**
     * @throws \OCP\DB\Schema\SchemaException
     */
    public function changeSchema(IOutput $output, Closure $schemaClosure, array $options): ?ISchemaWrapper
    {
        $schema = $schemaClosure();

        $table = $schema->getTable('my_table');
        $this->doSomething($table);

        return $schema;
    }

    private function doSomething(\OCP\DB\Schema\ITable $table): void
    {
        $table->addColumn('col', 'integer');
    }
}

?>
