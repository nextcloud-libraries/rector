<?php

namespace Nextcloud\Rector\Test\Rector\ImportNextcloudNamesRector\Fixture\First;

class SkipMultipleNamespaces
{
    public function foo(\OCP\IUser $user): void
    {
    }
}

namespace Nextcloud\Rector\Test\Rector\ImportNextcloudNamesRector\Fixture\Second;

class SkipMultipleNamespaces
{
    public function foo(\OCP\IUser $user): void
    {
    }
}

?>
