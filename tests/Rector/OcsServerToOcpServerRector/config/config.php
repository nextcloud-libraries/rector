<?php

declare(strict_types=1);

use ChristophWurst\Nextcloud\Rector\Rector\OcServerToOcpServerRector;
use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withRules([
        OcServerToOcpServerRector::class,
    ]);
