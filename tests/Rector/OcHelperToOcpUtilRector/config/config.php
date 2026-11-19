<?php

declare(strict_types=1);

use Nextcloud\Rector\Rector\OcHelperToOcpUtilRector;
use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withRules([
        OcHelperToOcpUtilRector::class,
    ]);
