<?php

// Simulate an app in isolation where Controller class is NOT available
// (i.e., OCP namespace is not resolved)

namespace Nextcloud\Rector\Test\Rector\RenameParameterRector\Fixture;

class AccountsController
{
    private string $currentUserId;

    public function __construct(string $appName, $UserId) {
        $this->currentUserId = $UserId;
    }
}

?>
-----
<?php

// Simulate an app in isolation where Controller class is NOT available
// (i.e., OCP namespace is not resolved)

namespace Nextcloud\Rector\Test\Rector\RenameParameterRector\Fixture;

class AccountsController
{
    private string $currentUserId;

    public function __construct(string $appName, $userId) {
        $this->currentUserId = $userId;
    }
}

?>
