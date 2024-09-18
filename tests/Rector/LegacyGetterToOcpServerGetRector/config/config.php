<?php

declare(strict_types=1);

use Nextcloud\Rector\Rector\LegacyGetterToOcpServerGetRector;
use Nextcloud\Rector\ValueObject\LegacyGetterToOcpServerGet;
use OCP\IRequest;
use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withConfiguredRule(
        LegacyGetterToOcpServerGetRector::class,
        [
            /** @phpstan-ignore class.notFound */
            new LegacyGetterToOcpServerGet('getRequest', IRequest::class),
        ],
    );
