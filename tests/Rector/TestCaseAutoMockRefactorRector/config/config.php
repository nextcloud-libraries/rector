<?php

declare(strict_types=1);

/*
 * SPDX-FileCopyrightText: 2024 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

use Nextcloud\Rector\Rector\TestCaseAutoMockRefactorRector;
use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withRules([
        TestCaseAutoMockRefactorRector::class,
    ]);
