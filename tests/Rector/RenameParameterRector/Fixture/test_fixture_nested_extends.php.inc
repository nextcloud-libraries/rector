<?php

namespace OCP\AppFramework;

abstract class Controller
{
    protected $appName;
    public function __construct(string $appName) {
        $this->appName = $appName;
    }
}

abstract class ApiController extends Controller {
}

namespace Nextcloud\Rector\Test\Rector\RenameParameterRector\Fixture;

class SomeClass extends \OCP\AppFramework\ApiController
{
    public function __construct(string $AppName) {
        parent::__construct($AppName);
    }
}

?>
-----
<?php

namespace OCP\AppFramework;

abstract class Controller
{
    protected $appName;
    public function __construct(string $appName) {
        $this->appName = $appName;
    }
}

abstract class ApiController extends Controller {
}

namespace Nextcloud\Rector\Test\Rector\RenameParameterRector\Fixture;

class SomeClass extends \OCP\AppFramework\ApiController
{
    public function __construct(string $appName) {
        parent::__construct($appName);
    }
}

?>
