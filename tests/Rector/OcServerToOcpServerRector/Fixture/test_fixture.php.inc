<?php

namespace Nextcloud\Rector\Test\Rector\OcServerToOcpServerRector\Fixture;

use OC;
use OCP\IRequest;

class SomeClass
{
    public function foo()
    {
        $request1 = \OC::$server->get(IRequest::class);
        $request2 = \OC::$server->get('\OCP\IRequest');
        $request3 = OC::$server->get(IRequest::class);
        $request4 = OC::$server->query(IRequest::class);
    }
}

?>
-----
<?php

namespace Nextcloud\Rector\Test\Rector\OcServerToOcpServerRector\Fixture;

use OC;
use OCP\IRequest;

class SomeClass
{
    public function foo()
    {
        $request1 = \OCP\Server::get(IRequest::class);
        $request2 = \OCP\Server::get('\OCP\IRequest');
        $request3 = \OCP\Server::get(IRequest::class);
        $request4 = \OCP\Server::get(IRequest::class);
    }
}

?>
