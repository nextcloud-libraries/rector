<?php

namespace ChristophWurst\Nextcloud\Rector\Test\Set\Nextcloud27\Fixture;

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

namespace ChristophWurst\Nextcloud\Rector\Test\Set\Nextcloud27\Fixture;

use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\AppFramework\Http\Attribute\PublicPage;
use OCP\AppFramework\Http\Attribute\NoCSRFRequired;

class SomeController
{
    #[NoAdminRequired]
    #[PublicPage]
    #[NoCSRFRequired]
    public function foo()
    {}
}

?>
