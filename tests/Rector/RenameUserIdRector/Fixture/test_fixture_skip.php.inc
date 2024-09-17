<?php

namespace ChristophWurst\Nextcloud\Rector\Test\Rector\RenameUserIdRector\Fixture;

class SomeClass
{
    public function __construct(string $appId, $UserId) {
    }
}

?>
-----
<?php

namespace ChristophWurst\Nextcloud\Rector\Test\Rector\RenameUserIdRector\Fixture;

class SomeClass
{
    public function __construct(string $appId, $UserId) {
    }
}

?>
