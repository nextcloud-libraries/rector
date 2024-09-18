<?php

declare(strict_types=1);

use Nextcloud\Rector\Rector\OcServerToOcpServerRector;
use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withRules([
        OcServerToOcpServerRector::class,
    ]);
