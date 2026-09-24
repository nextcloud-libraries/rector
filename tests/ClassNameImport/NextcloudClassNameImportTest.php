<?php

declare(strict_types=1);

/*
 * SPDX-FileCopyrightText: 2024 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace Nextcloud\Rector\Test\ClassNameImport;

use Iterator;
use Nextcloud\Rector\ClassNameImport\NextcloudNamespaceSkipVoter;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

#[RunTestsInSeparateProcesses]
final class NextcloudClassNameImportTest extends AbstractRectorTestCase
{
    protected function setUp(): void
    {
        /* The skip voters are injected when the ClassNameImportSkipper is first built, which happens before the config file is loaded */
        self::getContainer()->singleton(NextcloudNamespaceSkipVoter::class);
        parent::setUp();
    }

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
