<?php

namespace Nextcloud\Rector\Test\Rector\ImportNextcloudNamesRector\FixtureWithOtherRules;

class ImportNamesAddedByRules
{
    public function foo(): void
    {
        $request = \OC::$server->get(\OCP\IRequest::class);
    }
}

?>
-----
<?php

namespace Nextcloud\Rector\Test\Rector\ImportNextcloudNamesRector\FixtureWithOtherRules;

use OCP\Server;
use OCP\IRequest;

class ImportNamesAddedByRules
{
    public function foo(): void
    {
        $request = Server::get(IRequest::class);
    }
}

?>
