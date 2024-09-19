<?php

namespace OCP\AppFramework;

abstract class Controller
{
}

namespace Nextcloud\Rector\Test\Rector\RenameParameterRector\Fixture;

class SomeClass extends \OCP\AppFramework\Controller
{
    public function __construct($UserId) {
        $this->checkUserId($UserId);
    }

    private function checkUserId($userId)
    {
        // check the user id
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
    public function __construct($userId) {
        $this->checkUserId($userId);
    }

    private function checkUserId($userId)
    {
        // check the user id
    }
}

?>
