<?php

namespace Nextcloud\Rector\Test\Set\Fixture;

use OC;
use OCP\IRequest;

class SomeClass
{
    public function foo()
    {
        $request1 = \OC::$server->get(IRequest::class);
        $request2 = \OC::$server->get('\OCP\IRequest');
        $request3 = OC::$server->get(IRequest::class);
        script('mail', 'mail');
        $request4 = OC::$server->getRequest();
        $request5 = \OC::$server->getRequest();
        $contactManager = \OC::$server->getContactsManager();
    }
}

?>
-----
<?php

namespace Nextcloud\Rector\Test\Set\Fixture;

use OC;
use OCP\IRequest;

class SomeClass
{
    public function foo()
    {
        $request1 = \OCP\Server::get(IRequest::class);
        $request2 = \OCP\Server::get('\OCP\IRequest');
        $request3 = \OCP\Server::get(IRequest::class);
        \OCP\Util::addScript('mail', 'mail', 'core');
        $request4 = \OCP\Server::get(\OCP\IRequest::class);
        $request5 = \OCP\Server::get(\OCP\IRequest::class);
        $contactManager = \OCP\Server::get(\OCP\Contacts\IManager::class);
    }
}

?>
