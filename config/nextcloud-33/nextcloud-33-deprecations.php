<?php

declare(strict_types=1);

use Nextcloud\Rector\Rector\AnnotationToAttributeRector;
use Nextcloud\Rector\Rector\ReplaceFetchAllMethodCallRector;
use Nextcloud\Rector\Set\NextcloudSets;
use Rector\Config\RectorConfig;
use Rector\Php80\ValueObject\AnnotationToAttribute;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->sets([NextcloudSets::NEXTCLOUD_27]);
    $rectorConfig->rule(ReplaceFetchAllMethodCallRector::class);
    $rectorConfig->ruleWithConfiguration(
        AnnotationToAttributeRector::class,
        [
            new AnnotationToAttribute('NoSameSiteCookieRequired', 'OCP\AppFramework\Http\Attribute\NoSameSiteCookieRequired'),
            new AnnotationToAttribute('NoTwoFactorRequired', 'OCP\AppFramework\Http\Attribute\NoTwoFactorRequired'),
        ],
    );
};
