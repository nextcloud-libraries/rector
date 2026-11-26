<?php

declare(strict_types=1);

use Nextcloud\Rector\Rector\ImportNextcloudNamesRector;
use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withRules([
        ImportNextcloudNamesRector::class,
    ]);
