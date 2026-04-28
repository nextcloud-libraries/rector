<?php

/*
 * SPDX-FileCopyrightText: 2026 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace Nextcloud\Rector\Test\Set\Nextcloud32\Fixture;

use OCP\Mail\IMailer;

class SomeClass
{
    public function __construct(private IMailer $mailer)
    {
    }

    public function run(): void
    {
        $this->mailer->validateMailAddress('valid email?');
    }
}

?>
-----
<?php

/*
 * SPDX-FileCopyrightText: 2026 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace Nextcloud\Rector\Test\Set\Nextcloud32\Fixture;

use OCP\Mail\IMailer;

class SomeClass
{
    public function __construct(private IMailer $mailer, private \OCP\Mail\IEmailValidator $emailValidator)
    {
    }

    public function run(): void
    {
        $this->emailValidator->isValid('valid email?');
    }
}

?>
