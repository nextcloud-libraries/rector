<?php

namespace Nextcloud\Rector\Test\ClassNameImport\Fixture;

use OCP\Authentication\TwoFactorAuth;

class SomeClass
{
    public function foo()
    {
        $request1 = \OC::$server->get(\OCP\IRequest::class);
        $request2 = \OCP\Server::get(\OCP\IRequest::class);
        $class = new \OCA\My\CustomClass();
        $otherNamespace = new \Rector\Config\RectorConfig();
        $manager = \OCP\Server::get(\OCP\Share\IManager);
        $manager = \OCP\Server::get(TwoFactorAuth\IProvider);
        throw new \Exception('Class from root namespace');
    }
}

?>
-----
<?php

namespace Nextcloud\Rector\Test\ClassNameImport\Fixture;

use OCP\Authentication\TwoFactorAuth;
use OCP\IRequest;
use OCP\Server;
use OCA\My\CustomClass;

class SomeClass
{
    public function foo()
    {
        $request1 = \OC::$server->get(IRequest::class);
        $request2 = Server::get(IRequest::class);
        $class = new CustomClass();
        $otherNamespace = new \Rector\Config\RectorConfig();
        $manager = Server::get(\OCP\Share\IManager);
        $manager = Server::get(TwoFactorAuth\IProvider);
        throw new \Exception('Class from root namespace');
    }
}

?>
