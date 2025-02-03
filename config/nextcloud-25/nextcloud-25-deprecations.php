<?php

declare(strict_types=1);

use Nextcloud\Rector\Rector\OcServerToOcpServerRector;
use Nextcloud\Rector\Set\NextcloudSets;
use Rector\Arguments\Rector\MethodCall\RemoveMethodCallParamRector;
use Rector\Arguments\ValueObject\RemoveMethodCallParam;
use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\ValueObject\MethodCallRename;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->sets([NextcloudSets::NEXTCLOUD_24]);
    $rectorConfig->rules([
        OcServerToOcpServerRector::class,
    ]);
    $rectorConfig->ruleWithConfiguration(
        RemoveMethodCallParamRector::class,
        [
            // Remove job execute deprecated ILogger parameter
            new RemoveMethodCallParam('OCP\BackgroundJob\IJob', 'execute', 1),
        ],
    );
    $rectorConfig->ruleWithConfiguration(
        RenameMethodRector::class,
        [
            // Call start instead of execute method after rename
            new MethodCallRename('OCP\BackgroundJob\IJob', 'execute', 'start'),
        ],
    );
};
