<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2017 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Comments\Tests\Unit\Collaboration;

use OCA\Comments\Collaboration\CommentersSorter;
use OCP\Comments\IComment;
use OCP\Comments\ICommentsManager;
use PHPUnit\Framework\MockObject\MockObject;
use Test\TestCase;

class CommentersSorterTest extends TestCase {
	protected ICommentsManager&MockObject $commentsManager;
	protected CommentersSorter $sorter;

	#[\Override]
	protected function setUp(): void {
		parent::setUp();

		$this->commentsManager = $this->createMock(ICommentsManager::class);
		$this->commentsManager->method('m')
			->expects($this->any())
			->willReturn(true);

		$this->sorter = new CommentersSorter($this->commentsManager);
	}

	/**
	 * @param $data
	 */
	#[\PHPUnit\Framework\Attributes\DataProvider(methodName: 'sortDataProvider')]
	public function testSort($data): void {
		$this->commentsManager->expects($this->once())
			->method('getForObject')
			->willReturn([]);

		$workArray = $data['input'];
		$this->sorter->sort($workArray, ['itemType' => 'files', 'itemId' => '24']);

		$this->assertEquals($data['expected'], $workArray);
	}
}

?>
-----
<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2017 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Comments\Tests\Unit\Collaboration;

use OCA\Comments\Collaboration\CommentersSorter;
use OCP\Comments\IComment;
use OCP\Comments\ICommentsManager;
use PHPUnit\Framework\MockObject\MockObject;
use Test\TestCase;

class CommentersSorterTest extends TestCase {
	protected CommentersSorter $sorter;

	#[\Override]
	protected function setUp(): void {
		parent::setUp();

		$this->sorter = $this->createInstanceWithMocks(CommentersSorter::class);
        $this->mocks[ICommentsManager::class]->method('m')
			->expects($this->any())
			->willReturn(true);
	}

	/**
	 * @param $data
	 */
	#[\PHPUnit\Framework\Attributes\DataProvider(methodName: 'sortDataProvider')]
	public function testSort($data): void {
		$this->mocks[ICommentsManager::class]->expects($this->once())
			->method('getForObject')
			->willReturn([]);

		$workArray = $data['input'];
		$this->sorter->sort($workArray, ['itemType' => 'files', 'itemId' => '24']);

		$this->assertEquals($data['expected'], $workArray);
	}
}

?>
