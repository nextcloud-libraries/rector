<?php

declare(strict_types=1);

use ChristophWurst\Nextcloud\Rector\Rector\ILoggerToPsrLoggerLevelsRector;
use Rector\Config\RectorConfig;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(ILoggerToPsrLoggerLevelsRector::class);
};
