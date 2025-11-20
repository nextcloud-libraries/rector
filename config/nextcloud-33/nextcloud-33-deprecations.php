<?php

declare(strict_types=1);

use Nextcloud\Rector\Rector\ReplaceFetchAllMethodCallRector;
use Nextcloud\Rector\Set\NextcloudSets;
use Rector\Config\RectorConfig;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->sets([NextcloudSets::NEXTCLOUD_27]);
    $rectorConfig->rule(ReplaceFetchAllMethodCallRector::class);
};
