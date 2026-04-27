<?php

declare(strict_types=1);

use Nextcloud\Rector\Rector\ReplaceInjectedMethodCallRector;
use Nextcloud\Rector\Set\NextcloudSets;
use Nextcloud\Rector\ValueObject\ReplaceInjectedMethodCall;
use Rector\Config\RectorConfig;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->sets([NextcloudSets::NEXTCLOUD_29]);
    $rectorConfig->ruleWithConfiguration(
        ReplaceInjectedMethodCallRector::class,
        [
            new ReplaceInjectedMethodCall(
                'OCP\Mail\IMailer',
                'OCP\Mail\IEmailValidator',
                'emailValidator',
                [
                    'validateMailAddress' => 'isValid',
                ],
            ),
        ],
    );
};
