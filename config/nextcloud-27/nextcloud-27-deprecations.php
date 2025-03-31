<?php

declare(strict_types=1);

use Nextcloud\Rector\Rector\AnnotationToAttributeRector;
use Nextcloud\Rector\Set\NextcloudSets;
use Rector\Config\RectorConfig;
use Rector\Php80\ValueObject\AnnotationToAttribute;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->sets([NextcloudSets::NEXTCLOUD_26]);
    $rectorConfig->ruleWithConfiguration(
        AnnotationToAttributeRector::class,
        [
            new AnnotationToAttribute('AnonRateThrottle', 'OCP\AppFramework\Http\Attribute\AnonRateLimit'),
            // This one is commented out because the parameter would need to be transformed into several attributes
            // new AnnotationToAttribute(
            //     'AuthorizedAdminSetting',
            //     'OCP\AppFramework\Http\Attribute\AuthorizedAdminSetting',
            // ),
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
            new AnnotationToAttribute('UserRateThrottle', 'OCP\AppFramework\Http\Attribute\UserRateLimit'),
        ],
    );
};
