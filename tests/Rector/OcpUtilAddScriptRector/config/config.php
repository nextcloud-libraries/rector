<?php

declare(strict_types=1);

use Nextcloud\Rector\Rector\OcpUtilAddScriptRector;
use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withRules([
        OcpUtilAddScriptRector::class,
    ]);
