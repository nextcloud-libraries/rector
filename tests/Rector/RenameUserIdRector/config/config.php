<?php

declare(strict_types=1);

use ChristophWurst\Nextcloud\Rector\Rector\RenameUserIdRector;
use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withRules([
        RenameUserIdRector::class,
    ]);
