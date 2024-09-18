<?php

namespace OCP\AppFramework;

abstract class Controller
{

}

namespace Nextcloud\Rector\Test\Rector\RenameUserIdRector\Fixture;

class SomeClass extends \OCP\AppFramework\Controller
{
    public function __construct(string $appId, $UserId = null) {
    }
}

?>
-----
<?php

namespace OCP\AppFramework;

abstract class Controller
{

}

namespace Nextcloud\Rector\Test\Rector\RenameUserIdRector\Fixture;

class SomeClass extends \OCP\AppFramework\Controller
{
    public function __construct(string $appId, $userId = null) {
    }
}

?>
