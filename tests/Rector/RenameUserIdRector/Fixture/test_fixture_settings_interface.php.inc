<?php

namespace OCP\Settings;

interface ISettings {
}

namespace Nextcloud\Rector\Test\Rector\RenameUserIdRector\Fixture;

class SomeClass implements \OCP\Settings\ISettings
{
    public function __construct(string $appId, $UserId) {
    }
}

?>
-----
<?php

namespace OCP\Settings;

interface ISettings {
}

namespace Nextcloud\Rector\Test\Rector\RenameUserIdRector\Fixture;

class SomeClass implements \OCP\Settings\ISettings
{
    public function __construct(string $appId, $userId) {
    }
}

?>
