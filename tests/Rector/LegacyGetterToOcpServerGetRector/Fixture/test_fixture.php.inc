<?php

namespace Nextcloud\Rector\Test\Rector\OcServerToOcpServerRector\Fixture;

use OC;

class SomeClass
{
    public function foo()
    {
        $request1 = OC::$server->getRequest();
        $request2 = \OC::$server->getRequest();
        $contactManager = \OC::$server->getContactsManager();
    }
}

?>
-----
<?php

namespace Nextcloud\Rector\Test\Rector\OcServerToOcpServerRector\Fixture;

use OC;

class SomeClass
{
    public function foo()
    {
        $request1 = \OCP\Server::get(\OCP\IRequest::class);
        $request2 = \OCP\Server::get(\OCP\IRequest::class);
        $contactManager = \OCP\Server::get(\OCP\Contacts\IManager::class);
    }
}

?>
