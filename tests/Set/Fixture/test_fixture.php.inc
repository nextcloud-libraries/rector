<?php

namespace ChristophWurst\Nextcloud\Rector\Test\Set\Fixture;

use OC;
use OCP\IRequest;
use OCA\Some\Plugin;

class SomeClass
{
    public function foo()
    {
        $request1 = \OC::$server->get(IRequest::class);
        $request2 = \OC::$server->get('\OCP\IRequest');
        $request3 = OC::$server->get(IRequest::class);
        $request4 = OC::$server->get(\OCP\IRequest::class);
        $plugin = OC::$server->get(\OCP\Other\Plugin::class);
        script('mail', 'mail');
    }
}

?>
-----
<?php

namespace ChristophWurst\Nextcloud\Rector\Test\Set\Fixture;

use OCP\Server;
use OC;
use OCP\IRequest;
use OCA\Some\Plugin;

class SomeClass
{
    public function foo()
    {
        $request1 = Server::get(IRequest::class);
        $request2 = Server::get('\OCP\IRequest');
        $request3 = Server::get(IRequest::class);
        $request4 = Server::get(IRequest::class);
        $plugin = Server::get(\OCP\Other\Plugin::class);
        \OCP\Util::addScript('mail', 'mail', 'core');
    }
}

?>
