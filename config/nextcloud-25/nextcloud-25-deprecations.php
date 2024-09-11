<?php

declare(strict_types=1);

use Christophwurst\Nextcloud\Rector\Rector\OcServerToOcpServerRector;
use Rector\Config\RectorConfig;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rules([
        OcServerToOcpServerRector::class,
    ]);
};