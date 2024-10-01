<?php

namespace OCP\AppFramework;

abstract class Controller
{
}

namespace Nextcloud\Rector\Test\Rector\RenameParameterRector\Fixture;

class SomeClass extends \OCP\AppFramework\Controller
{
    private $userId;

    public function __construct(string $appId, $UserId = null) {
        $this->userId = $UserId;
    }
}

?>
-----
<?php

namespace OCP\AppFramework;

abstract class Controller
{
}

namespace Nextcloud\Rector\Test\Rector\RenameParameterRector\Fixture;

class SomeClass extends \OCP\AppFramework\Controller
{
    private $userId;

    public function __construct(string $appId, $userId = null) {
        $this->userId = $userId;
    }
}

?>