<?php

declare(strict_types=1);

use Nextcloud\Rector\Set\NextcloudSets;
use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withImportNames(removeUnusedImports: false)
    ->withSets([
        NextcloudSets::NEXTCLOUD_29,
    ]);
