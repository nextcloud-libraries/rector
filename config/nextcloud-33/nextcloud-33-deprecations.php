<?php

declare(strict_types=1);

use Nextcloud\Rector\Rector\AnnotationToAttributeRector;
use Nextcloud\Rector\Rector\ReplaceFetchAllMethodCallRector;
use Nextcloud\Rector\Rector\ReplaceInjectedMethodCallRector;
use Nextcloud\Rector\Set\NextcloudSets;
use Nextcloud\Rector\ValueObject\ReplaceInjectedMethodCall;
use Rector\Config\RectorConfig;
use Rector\Php80\ValueObject\AnnotationToAttribute;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->sets([NextcloudSets::NEXTCLOUD_29]);
    $rectorConfig->rule(ReplaceFetchAllMethodCallRector::class);
    $rectorConfig->ruleWithConfiguration(
        ReplaceInjectedMethodCallRector::class,
        [
            new ReplaceInjectedMethodCall(
                'OCP\IConfig',
                'OCP\IUserConfig',
                'userConfig',
                [
                    'getAllUserValues' => 'getAllValues',
                    'getUserKeys' => 'getKeys',
                    'getUserValue' => 'getValueString',
                    'getUserValueForUsers' => 'getValuesByUsers',
                    'getUsersForUserValue' => 'searchUsersByValueString',
                    'setUserValue' => 'setValueString',
                    'deleteUserValue' => 'deleteUserConfig',
                    'deleteAllUserValues' => 'deleteAllUserConfig',
                    'deleteAppFromAllUsers' => 'deleteApp',
                ],
            ),
        ],
    );
    $rectorConfig->ruleWithConfiguration(
        AnnotationToAttributeRector::class,
        [
            new AnnotationToAttribute(
                'NoSameSiteCookieRequired',
                'OCP\AppFramework\Http\Attribute\NoSameSiteCookieRequired',
            ),
            new AnnotationToAttribute('NoTwoFactorRequired', 'OCP\AppFramework\Http\Attribute\NoTwoFactorRequired'),
        ],
    );
};
