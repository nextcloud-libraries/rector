<?php

declare(strict_types=1);

use ChristophWurst\Nextcloud\Rector\Set\NextcloudSets;
use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withSets([
        NextcloudSets::NEXTCLOUD_ALL,
        NextcloudSets::NEXTCLOUD_25,
    ]);
