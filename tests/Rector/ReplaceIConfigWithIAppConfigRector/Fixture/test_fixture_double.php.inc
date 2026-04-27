<?php

/*
 * SPDX-FileCopyrightText: 2026 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace Nextcloud\Rector\Test\Rector\ReplaceIConfigWithIAppConfigRector\Fixture;

use OCP\IAppConfig;
use OCP\IConfig;

class SomeClass
{
    public function __construct(private IConfig $config, private IAppConfig $appConfig)
    {
    }

    public function run(): void
    {
        $this->config->getAppValue('myapp', 'mykey');
        $this->config->getAppValue('myapp', 'mykey', 'default');
        $this->config->getAppKeys('myapp');
        $this->config->setAppValue('myapp', 'mykey', 'value');
        $this->config->deleteAppValue('myapp', 'mykey');
        $this->config->deleteAppValues('myapp');
    }
}

?>
-----
<?php

/*
 * SPDX-FileCopyrightText: 2026 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace Nextcloud\Rector\Test\Rector\ReplaceIConfigWithIAppConfigRector\Fixture;

use OCP\IAppConfig;
use OCP\IConfig;

class SomeClass
{
    public function __construct(private IConfig $config, private IAppConfig $appConfig)
    {
    }

    public function run(): void
    {
        $this->appConfig->getValue('myapp', 'mykey');
        $this->appConfig->getValue('myapp', 'mykey', 'default');
        $this->appConfig->getKeys('myapp');
        $this->appConfig->setValue('myapp', 'mykey', 'value');
        $this->appConfig->deleteKey('myapp', 'mykey');
        $this->appConfig->deleteApp('myapp');
    }
}

?>
