<?php

/*
 * SPDX-FileCopyrightText: 2026 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace Nextcloud\Rector\Test\Rector\ReplaceIConfigWithIAppConfigRector\Fixture;

use OCP\AppFramework\Services\IAppConfig;
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

use OCP\AppFramework\Services\IAppConfig;
use OCP\IConfig;

class SomeClass
{
    public function __construct(private IConfig $config, private IAppConfig $appConfig, private \OCP\IAppConfig $appConfig2)
    {
    }

    public function run(): void
    {
        $this->appConfig2->getValue('myapp', 'mykey');
        $this->appConfig2->getValue('myapp', 'mykey', 'default');
        $this->appConfig2->getKeys('myapp');
        $this->appConfig2->setValue('myapp', 'mykey', 'value');
        $this->appConfig2->deleteKey('myapp', 'mykey');
        $this->appConfig2->deleteApp('myapp');
    }
}

?>
