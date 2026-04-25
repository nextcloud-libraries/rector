<?php

declare(strict_types=1);

namespace Nextcloud\Rector\Set;

final class NextcloudSets
{
    public const NEXTCLOUD_25 = __DIR__ . '/../../config/nextcloud-25/nextcloud-25-deprecations.php';
    public const NEXTCLOUD_26 = __DIR__ . '/../../config/nextcloud-26/nextcloud-26-deprecations.php';
    public const NEXTCLOUD_27 = __DIR__ . '/../../config/nextcloud-27/nextcloud-27-deprecations.php';
    public const NEXTCLOUD_28 = self::NEXTCLOUD_27;
    public const NEXTCLOUD_29 = __DIR__ . '/../../config/nextcloud-29/nextcloud-29-deprecations.php';
    public const NEXTCLOUD_30 = self::NEXTCLOUD_29;
    public const NEXTCLOUD_31 = self::NEXTCLOUD_30;
    public const NEXTCLOUD_32 = self::NEXTCLOUD_31;
    public const NEXTCLOUD_33 = __DIR__ . '/../../config/nextcloud-33/nextcloud-33-deprecations.php';
}
