<?php

declare(strict_types=1);

use Nextcloud\Rector\Rector\OrderBySortDirectionRector;
use Nextcloud\Rector\Rector\TypeLookupNameToGetNameRector;
use Nextcloud\Rector\Set\NextcloudSets;
use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\ClassConstFetch\RenameClassConstFetchRector;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Renaming\ValueObject\RenameClassAndConstFetch;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->sets([NextcloudSets::NEXTCLOUD_34]);
    $rectorConfig->rule(OrderBySortDirectionRector::class);
    $rectorConfig->rule(TypeLookupNameToGetNameRector::class);
    $rectorConfig->skip([
        // These are the server-internal classes implementing the OCP\DB\Schema wrapper itself,
        // they still work with the raw Doctrine\DBAL\Types\Type and must not be rewritten.
        TypeLookupNameToGetNameRector::class => [
            'lib/private/DB/MigrationService.php',
            'lib/private/DB/Schema/Column.php',
        ],
    ]);
    $rectorConfig->ruleWithConfiguration(
        RenameClassRector::class,
        [
            'Doctrine\DBAL\Schema\Table' => 'OCP\DB\Schema\ITable',
            'Doctrine\DBAL\Schema\Column' => 'OCP\DB\Schema\IColumn',
            'Doctrine\DBAL\Schema\Index' => 'OCP\DB\Schema\IIndex',
            'Doctrine\DBAL\Schema\ForeignKeyConstraint' => 'OCP\DB\Schema\IForeignKeyConstraint',
            'Doctrine\DBAL\Schema\SchemaException' => 'OCP\DB\Schema\SchemaException',
        ],
    );
    $rectorConfig->ruleWithConfiguration(
        RenameClassConstFetchRector::class,
        [
            new RenameClassAndConstFetch('OCP\DB\Types', 'BIGINT', 'OCP\DB\Schema\ColumnType', 'Bigint'),
            new RenameClassAndConstFetch('OCP\DB\Types', 'BINARY', 'OCP\DB\Schema\ColumnType', 'Binary'),
            new RenameClassAndConstFetch('OCP\DB\Types', 'BLOB', 'OCP\DB\Schema\ColumnType', 'Blob'),
            new RenameClassAndConstFetch('OCP\DB\Types', 'BOOLEAN', 'OCP\DB\Schema\ColumnType', 'Boolean'),
            new RenameClassAndConstFetch('OCP\DB\Types', 'DATE', 'OCP\DB\Schema\ColumnType', 'Date'),
            new RenameClassAndConstFetch(
                'OCP\DB\Types',
                'DATE_IMMUTABLE',
                'OCP\DB\Schema\ColumnType',
                'DateImmutable',
            ),
            new RenameClassAndConstFetch('OCP\DB\Types', 'DATETIME', 'OCP\DB\Schema\ColumnType', 'Datetime'),
            new RenameClassAndConstFetch(
                'OCP\DB\Types',
                'DATETIME_IMMUTABLE',
                'OCP\DB\Schema\ColumnType',
                'DatetimeImmutable',
            ),
            new RenameClassAndConstFetch('OCP\DB\Types', 'DATETIME_TZ', 'OCP\DB\Schema\ColumnType', 'DatetimeTz'),
            new RenameClassAndConstFetch(
                'OCP\DB\Types',
                'DATETIME_TZ_IMMUTABLE',
                'OCP\DB\Schema\ColumnType',
                'DatetimeTzImmutable',
            ),
            new RenameClassAndConstFetch('OCP\DB\Types', 'DECIMAL', 'OCP\DB\Schema\ColumnType', 'Decimal'),
            new RenameClassAndConstFetch('OCP\DB\Types', 'FLOAT', 'OCP\DB\Schema\ColumnType', 'Float'),
            new RenameClassAndConstFetch('OCP\DB\Types', 'INTEGER', 'OCP\DB\Schema\ColumnType', 'Integer'),
            new RenameClassAndConstFetch('OCP\DB\Types', 'SMALLINT', 'OCP\DB\Schema\ColumnType', 'Smallint'),
            new RenameClassAndConstFetch('OCP\DB\Types', 'STRING', 'OCP\DB\Schema\ColumnType', 'String'),
            new RenameClassAndConstFetch('OCP\DB\Types', 'TEXT', 'OCP\DB\Schema\ColumnType', 'Text'),
            new RenameClassAndConstFetch('OCP\DB\Types', 'TIME', 'OCP\DB\Schema\ColumnType', 'Time'),
            new RenameClassAndConstFetch(
                'OCP\DB\Types',
                'TIME_IMMUTABLE',
                'OCP\DB\Schema\ColumnType',
                'TimeImmutable',
            ),
            new RenameClassAndConstFetch('OCP\DB\Types', 'JSON', 'OCP\DB\Schema\ColumnType', 'Json'),
        ],
    );
};
