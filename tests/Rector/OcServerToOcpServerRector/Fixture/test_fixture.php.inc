<?php

namespace ChristophWurst\Nextcloud\Rector\Test\Rector\OcServerToOcpServerRector\Fixture;

use OC;
use OCP\IRequest;

class SomeClass
{
    public function foo()
    {
        $request1 = \OC::$server->get(IRequest::class);
        $request2 = \OC::$server->get('\OCP\IRequest');
        $request3 = OC::$server->get(\OCP\IRequest::class);
    }
}

?>
-----
<?php

namespace ChristophWurst\Nextcloud\Rector\Test\Rector\OcServerToOcpServerRector\Fixture;

use OCP\Server;
use OC;
use OCP\IRequest;

class SomeClass
{
    public function foo()
    {
        $request1 = Server::get(IRequest::class);
        $request2 = Server::get('\OCP\IRequest');
        $request3 = Server::get(IRequest::class);
    }
}

?>
