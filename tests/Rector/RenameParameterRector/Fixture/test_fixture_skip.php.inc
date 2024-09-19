<?php

namespace Nextcloud\Rector\Test\Rector\RenameParameterRector\Fixture;

class SomeClassTwo
{
    public function __construct(string $AppName, $UserId) {
    }
}

?>
-----
<?php

namespace Nextcloud\Rector\Test\Rector\RenameParameterRector\Fixture;

class SomeClassTwo
{
    public function __construct(string $AppName, $UserId) {
    }
}

?>
