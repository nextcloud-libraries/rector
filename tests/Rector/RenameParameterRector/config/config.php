<?php

declare(strict_types=1);

use Nextcloud\Rector\Rector\RenameParameterRector;
use Nextcloud\Rector\ValueObject\RenameParameter;
use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withConfiguredRule(
        RenameParameterRector::class,
        [
            new RenameParameter('UserId', 'userId'),
            new RenameParameter('AppName', 'appName'),
        ],
    );
