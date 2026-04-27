<?php

declare(strict_types=1);

/*
 * SPDX-FileCopyrightText: 2026 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

use Nextcloud\Rector\Rector\ReplaceIConfigWithIUserConfigRector;
use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withRules([
        ReplaceIConfigWithIUserConfigRector::class,
    ]);
