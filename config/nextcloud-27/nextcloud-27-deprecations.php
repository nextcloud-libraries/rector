<?php

declare(strict_types=1);

use Nextcloud\Rector\Set\NextcloudSets;
use Rector\Config\RectorConfig;
use Rector\Php80\Rector\Class_\AnnotationToAttributeRector;
use Rector\Php80\ValueObject\AnnotationToAttribute;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->sets([NextcloudSets::NEXTCLOUD_26]);
    $rectorConfig->ruleWithConfiguration(
        AnnotationToAttributeRector::class,
        [
            new AnnotationToAttribute('AnonRateLimit', 'OCP\AppFramework\Http\Attribute\AnonRateLimit'),
            new AnnotationToAttribute('ARateLimit', 'OCP\AppFramework\Http\Attribute\ARateLimit'),
            new AnnotationToAttribute(
                'AuthorizedAdminSetting',
                'OCP\AppFramework\Http\Attribute\AuthorizedAdminSetting',
            ),
            new AnnotationToAttribute('BruteForceProtection', 'OCP\AppFramework\Http\Attribute\BruteForceProtection'),
            new AnnotationToAttribute('CORS', 'OCP\AppFramework\Http\Attribute\CORS'),
            new AnnotationToAttribute('NoAdminRequired', 'OCP\AppFramework\Http\Attribute\NoAdminRequired'),
            new AnnotationToAttribute('NoCSRFRequired', 'OCP\AppFramework\Http\Attribute\NoCSRFRequired'),
            new AnnotationToAttribute(
                'PasswordConfirmationRequired',
                'OCP\AppFramework\Http\Attribute\PasswordConfirmationRequired',
            ),
            new AnnotationToAttribute('PublicPage', 'OCP\AppFramework\Http\Attribute\PublicPage'),
            new AnnotationToAttribute('StrictCookiesRequired', 'OCP\AppFramework\Http\Attribute\StrictCookiesRequired'),
            new AnnotationToAttribute('SubAdminRequired', 'OCP\AppFramework\Http\Attribute\SubAdminRequired'),
            new AnnotationToAttribute('UserRateLimit', 'OCP\AppFramework\Http\Attribute\UserRateLimit'),
        ],
    );
};
