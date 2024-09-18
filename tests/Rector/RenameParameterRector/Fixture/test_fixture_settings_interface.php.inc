<?php

namespace OCP\Settings;

interface ISettings {
}

namespace Nextcloud\Rector\Test\Rector\RenameParameterRector\Fixture;

class SomeClass implements \OCP\Settings\ISettings
{
    private $userId;

    public function __construct(string $appId, $UserId) {
        $this->userId = $UserId;
    }
}

?>
-----
<?php

namespace OCP\Settings;

interface ISettings {
}

namespace Nextcloud\Rector\Test\Rector\RenameParameterRector\Fixture;

class SomeClass implements \OCP\Settings\ISettings
{
    private $userId;

    public function __construct(string $appId, $userId) {
        $this->userId = $userId;
    }
}

?>
