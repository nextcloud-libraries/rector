<?php

namespace Nextcloud\Rector\Test\Rector\RenameUserIdRector\Fixture;

class SomeClass
{
    public function __construct(string $appId, $UserId) {
    }
}

?>
-----
<?php

namespace Nextcloud\Rector\Test\Rector\RenameUserIdRector\Fixture;

class SomeClass
{
    public function __construct(string $appId, $UserId) {
    }
}

?>
