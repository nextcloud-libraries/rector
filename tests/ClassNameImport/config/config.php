<?php

declare(strict_types=1);

use Nextcloud\Rector\ClassNameImport\NextcloudNamespaceSkipVoter;
use Rector\CodingStyle\Contract\ClassNameImport\ClassNameImportSkipVoterInterface;
use Rector\Config\RectorConfig;

$config = RectorConfig::configure()
    ->withImportNames(importShortClasses:false);

$config->registerService(NextcloudNamespaceSkipVoter::class, tag:ClassNameImportSkipVoterInterface::class);

return $config;
