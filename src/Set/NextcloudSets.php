<?php

declare(strict_types=1);

namespace ChristophWurst\Nextcloud\Rector\Set;

use Rector\Set\Contract\SetListInterface;

/**
 * @psalm-suppress DeprecatedInterface
 */
final class NextcloudSets implements SetListInterface
{
    public const NEXTCLOUD_ALL = __DIR__ . '/../../config/nextcloud-all/nextcloud-all-deprecations.php';
    public const NEXTCLOUD_25 = __DIR__ . '/../../config/nextcloud-25/nextcloud-25-deprecations.php';
}
