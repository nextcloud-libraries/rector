<?php

namespace Nextcloud\Rector\Test\Rector\ImportNextcloudNamesRector\Fixture;

use OCP\IConfig;

class SkipConflictingShortName
{
    public function foo(IConfig $config, \OC\SystemConfig\IConfig $systemConfig): void
    {
    }
}

?>
