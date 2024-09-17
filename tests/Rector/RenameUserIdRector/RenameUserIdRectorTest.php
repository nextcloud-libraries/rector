<?php

declare(strict_types=1);

/*
 * SPDX-FileCopyrightText: 2024 Daniel Kesselberg <mail@danielkesselberg.de>
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace ChristophWurst\Nextcloud\Rector\Test\Rector\RenameUserIdRector;

use Iterator;
use PHPUnit\Framework\Attributes\DataProvider;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class RenameUserIdRectorTest extends AbstractRectorTestCase
{
    #[DataProvider('provideData')]
    public function test(string $filePath): void
    {
        $this->doTestFile($filePath);
    }

    public static function provideData(): Iterator
    {
        return self::yieldFilesFromDirectory(__DIR__ . '/Fixture');
    }

    public function provideConfigFilePath(): string
    {
        return __DIR__ . '/config/config.php';
    }
}
