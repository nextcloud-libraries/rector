<?php

declare(strict_types=1);

use Nextcloud\Rector\Rector\ReplaceInjectedMethodCallRector;
use Nextcloud\Rector\Set\NextcloudSets;
use Nextcloud\Rector\ValueObject\ReplaceInjectedMethodCall;
use Rector\Config\RectorConfig;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->sets([NextcloudSets::NEXTCLOUD_27]);
    $rectorConfig->ruleWithConfiguration(
        ReplaceInjectedMethodCallRector::class,
        [
            new ReplaceInjectedMethodCall(
                'OCP\IConfig',
                'OCP\IAppConfig',
                'appConfig',
                [
                    'getAppValue' => 'getValue',
                    'getAppKeys' => 'getKeys',
                    'setAppValue' => 'setValue',
                    'deleteAppValue' => 'deleteKey',
                    'deleteAppValues' => 'deleteApp',
                ],
            ),
        ],
    );
};
