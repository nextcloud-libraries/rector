<?php

namespace Nextcloud\Rector\Test\Rector\ImportNextcloudNamesRector\Fixture;

use OCP\Authentication\TwoFactorAuth;

class ImportNextcloudNames
{
    public function foo(\OCP\IUser $user): \OCA\My\CustomClass
    {
        $request = \OCP\Server::get(\OCP\IRequest::class);
        $manager = \OCP\Server::get(\OCP\Share\IManager::class);
        $provider = \OCP\Server::get(TwoFactorAuth\IProvider::class);
        $config = new \Rector\Config\RectorConfig();
        $logger = \OCP\Server::get(\Psr\Log\LoggerInterface::class);
        throw new \Exception('Class from root namespace');
    }
}

?>
-----
<?php

namespace Nextcloud\Rector\Test\Rector\ImportNextcloudNamesRector\Fixture;

use OCP\IUser;
use OCA\My\CustomClass;
use OCP\Server;
use OCP\IRequest;
use OCP\Authentication\TwoFactorAuth;

class ImportNextcloudNames
{
    public function foo(IUser $user): CustomClass
    {
        $request = Server::get(IRequest::class);
        $manager = Server::get(\OCP\Share\IManager::class);
        $provider = Server::get(TwoFactorAuth\IProvider::class);
        $config = new \Rector\Config\RectorConfig();
        $logger = Server::get(\Psr\Log\LoggerInterface::class);
        throw new \Exception('Class from root namespace');
    }
}

?>
