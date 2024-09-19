<?php

declare(strict_types=1);

use Nextcloud\Rector\Rector\RenameParameterRector;
use Nextcloud\Rector\ValueObject\RenameParameter;
use Rector\Config\RectorConfig;
use Rector\Php80\Rector\Class_\AnnotationToAttributeRector;
use Rector\Php80\ValueObject\AnnotationToAttribute;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->ruleWithConfiguration(
        AnnotationToAttributeRector::class,
        [
            new AnnotationToAttribute('UseSession', 'OCP\AppFramework\Http\Attribute\UseSession'),
        ],
    );
    $rectorConfig->ruleWithConfiguration(
        RenameParameterRector::class,
        [
            new RenameParameter('AppName', 'appName'),
            new RenameParameter('UserId', 'userId'),
            new RenameParameter('WebRoot', 'webRoot'),
        ],
    );
};
