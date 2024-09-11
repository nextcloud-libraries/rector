<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Php80\Rector\Class_\AnnotationToAttributeRector;
use Rector\Php80\ValueObject\AnnotationToAttribute;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->ruleWithConfiguration(
        AnnotationToAttributeRector::class,
        [
            new AnnotationToAttribute('NoAdminRequired', 'OCP\AppFramework\Http\Attribute\NoAdminRequired'),
            new AnnotationToAttribute('PublicPage', 'OCP\AppFramework\Http\Attribute\PublicPage'),
            new AnnotationToAttribute('NoCSRFRequired', 'OCP\AppFramework\Http\Attribute\NoCSRFRequired'),
        ],
    );
};
