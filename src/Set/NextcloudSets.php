<?php

declare(strict_types=1);

namespace Nextcloud\Rector\Set;

use Rector\Set\Contract\SetListInterface;

/**
 * @psalm-suppress DeprecatedInterface
 */
final class NextcloudSets implements SetListInterface
{
    public const NEXTCLOUD_25 = __DIR__ . '/../../config/nextcloud-25/nextcloud-25-deprecations.php';
    public const NEXTCLOUD_26 = __DIR__ . '/../../config/nextcloud-26/nextcloud-26-deprecations.php';
    public const NEXTCLOUD_27 = __DIR__ . '/../../config/nextcloud-27/nextcloud-27-deprecations.php';
    public const NEXTCLOUD_28 = self::NEXTCLOUD_27;
    public const NEXTCLOUD_29 = self::NEXTCLOUD_27;
    public const NEXTCLOUD_30 = self::NEXTCLOUD_27;
}
