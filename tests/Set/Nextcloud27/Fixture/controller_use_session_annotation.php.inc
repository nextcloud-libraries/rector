<?php

namespace Nextcloud\Rector\Test\Set\Nextcloud27\Fixture;

class SomeController
{
    /**
     * @NoAdminRequired
     * @PublicPage
     * @NoCSRFRequired
     */
    public function foo()
    {}
}

?>
-----
<?php

namespace Nextcloud\Rector\Test\Set\Nextcloud27\Fixture;

class SomeController
{
    #[\OCP\AppFramework\Http\Attribute\NoAdminRequired]
    #[\OCP\AppFramework\Http\Attribute\PublicPage]
    #[\OCP\AppFramework\Http\Attribute\NoCSRFRequired]
    public function foo()
    {}
}

?>
