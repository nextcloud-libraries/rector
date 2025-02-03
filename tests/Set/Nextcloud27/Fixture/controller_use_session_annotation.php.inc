<?php

namespace Nextcloud\Rector\Test\Set\Nextcloud27\Fixture;

class SomeController
{
    /**
     * @AnonRateThrottle(limit=10, period=100)
     * @UserRateThrottle(limit=20, period=200)
     * @AuthorizedAdminSetting(settings=OCA\NotesTutorial\Settings\NotesAdmin;OCA\NotesTutorial\Settings\NotesSubAdmin)
     * @BruteForceProtection(action=login)
     * @CORS
     * @NoAdminRequired
     * @NoCSRFRequired
     * @PasswordConfirmationRequired
     * @PublicPage
     * @StrictCookiesRequired
     * @SubAdminRequired
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
    #[\OCP\AppFramework\Http\Attribute\AnonRateLimit(limit: 10, period: 100)]
    #[\OCP\AppFramework\Http\Attribute\UserRateLimit(limit: 20, period: 200)]
    #[\OCP\AppFramework\Http\Attribute\AuthorizedAdminSetting(settings: 'OCA\NotesTutorial\Settings\NotesAdmin')]
    #[\OCP\AppFramework\Http\Attribute\AuthorizedAdminSetting(settings: 'OCA\NotesTutorial\Settings\NotesSubAdmin')]
    #[\OCP\AppFramework\Http\Attribute\BruteForceProtection(action: 'login')]
    #[\OCP\AppFramework\Http\Attribute\CORS]
    #[\OCP\AppFramework\Http\Attribute\NoAdminRequired]
    #[\OCP\AppFramework\Http\Attribute\NoCSRFRequired]
    #[\OCP\AppFramework\Http\Attribute\PasswordConfirmationRequired]
    #[\OCP\AppFramework\Http\Attribute\PublicPage]
    #[\OCP\AppFramework\Http\Attribute\StrictCookiesRequired]
    #[\OCP\AppFramework\Http\Attribute\SubAdminRequired]
    public function foo()
    {}
}

?>
