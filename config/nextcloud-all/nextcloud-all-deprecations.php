<?php

declare(strict_types=1);

use ChristophWurst\Nextcloud\Rector\Rector\OcpUtilAddScriptRector;
use Rector\Config\RectorConfig;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rules([
        OcpUtilAddScriptRector::class,
    ]);
};