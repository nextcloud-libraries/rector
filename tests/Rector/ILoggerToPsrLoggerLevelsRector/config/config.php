<?php

declare(strict_types=1);

use ChristophWurst\Nextcloud\Rector\Rector\ILoggerToPsrLoggerLevelsRector;
use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withRules([
        ILoggerToPsrLoggerLevelsRector::class,
    ]);
