<?php

declare(strict_types=1);

use ChristophWurst\Nextcloud\Rector\Rector\RenameUserIdRector;
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
    $rectorConfig->rule(RenameUserIdRector::class);
};
