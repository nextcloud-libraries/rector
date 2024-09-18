<?php

namespace OCP\AppFramework;

abstract class Controller
{

}

namespace Nextcloud\Rector\Test\Rector\RenameUserIdRector\Fixture;

class SomeClass extends \OCP\AppFramework\Controller
{
    public function __construct() {
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
    public function __construct() {
    }
}

?>
