<?php

/*
 * SPDX-FileCopyrightText: 2026 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace Nextcloud\Rector\Test\Set\Nextcloud33\Fixture;

use OCP\IConfig;

class SomeClass
{
    public function __construct(private IConfig $config)
    {
    }

    public function run(): void
    {
        $this->config->getUserValue('user', 'myapp', 'mykey');
        $this->config->getUserValue('user', 'myapp', 'mykey', 'default');
        $this->config->getUserKeys('user', 'myapp');
        $this->config->setUserValue('user', 'myapp', 'mykey', 'value');
        $this->config->deleteUserValue('user', 'myapp', 'mykey');
        $this->config->deleteAppFromAllUsers('myapp');
    }
}

?>
-----
<?php

/*
 * SPDX-FileCopyrightText: 2026 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace Nextcloud\Rector\Test\Set\Nextcloud33\Fixture;

use OCP\IConfig;

class SomeClass
{
    public function __construct(private IConfig $config, private \OCP\IUserConfig $userConfig)
    {
    }

    public function run(): void
    {
        $this->userConfig->getValueString('user', 'myapp', 'mykey');
        $this->userConfig->getValueString('user', 'myapp', 'mykey', 'default');
        $this->userConfig->getKeys('user', 'myapp');
        $this->userConfig->setValueString('user', 'myapp', 'mykey', 'value');
        $this->userConfig->deleteUserConfig('user', 'myapp', 'mykey');
        $this->userConfig->deleteApp('myapp');
    }
}

?>
