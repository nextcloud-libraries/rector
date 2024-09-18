<?php

namespace Nextcloud\Rector\Test\Set\Nextcloud26\Fixture;

class SomeController
{
    /**
     * @UseSession
     */
    public function foo()
    {}
}

?>
-----
<?php

namespace Nextcloud\Rector\Test\Set\Nextcloud26\Fixture;

class SomeController
{
    #[\OCP\AppFramework\Http\Attribute\UseSession]
    public function foo()
    {}
}

?>
