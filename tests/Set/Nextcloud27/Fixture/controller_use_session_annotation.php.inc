<?php

namespace Nextcloud\Rector\Test\Set\Nextcloud27\Fixture;

class SomeController
{
    /**
     * @AnonRateLimit
     * @ARateLimit
     * @AuthorizedAdminSetting
     * @BruteForceProtection
     * @CORS
     * @NoAdminRequired
     * @NoCSRFRequired
     * @PasswordConfirmationRequired
     * @PublicPage
     * @StrictCookiesRequired
     * @SubAdminRequired
     * @UserRateLimit
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
    #[\OCP\AppFramework\Http\Attribute\AnonRateLimit]
    #[\OCP\AppFramework\Http\Attribute\ARateLimit]
    #[\OCP\AppFramework\Http\Attribute\AuthorizedAdminSetting]
    #[\OCP\AppFramework\Http\Attribute\BruteForceProtection]
    #[\OCP\AppFramework\Http\Attribute\CORS]
    #[\OCP\AppFramework\Http\Attribute\NoAdminRequired]
    #[\OCP\AppFramework\Http\Attribute\NoCSRFRequired]
    #[\OCP\AppFramework\Http\Attribute\PasswordConfirmationRequired]
    #[\OCP\AppFramework\Http\Attribute\PublicPage]
    #[\OCP\AppFramework\Http\Attribute\StrictCookiesRequired]
    #[\OCP\AppFramework\Http\Attribute\SubAdminRequired]
    #[\OCP\AppFramework\Http\Attribute\UserRateLimit]
    public function foo()
    {}
}

?>
