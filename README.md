# nextcloud/rector

Rector upgrade rules for Nextcloud

## About

This is a package containing rector rules and sets to use to upgrade your Nextcloud application to the latest API changes.

This project adheres to a [code of conduct](CODE_OF_CONDUCT.md).
By participating in this project and its community, you are expected to uphold this code.


## Installation

Install this package as a dependency using [Composer](https://getcomposer.org). We recommend to do so in a vendor bin directory along with rector.

``` bash
composer require --dev bamarni/composer-bin-plugin
composer bin rector require rector/rector --dev
composer bin rector require nextcloud/rector --dev
```

## Usage

First generate a rector.php configuration by running `process` command a first time:
```bash
./vendor/bin/rector process
```

We recommend that you first run rector with an empty configuration, commit the result, and then add the sets from Nextcloud
 and PHP one by one and commit the rule along with its result each time.
You could end up with a configuration like this one:

``` php
<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2024 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-only
 */

use Nextcloud\Rector\Set\NextcloudSets;
use Rector\Config\RectorConfig;

return RectorConfig::configure()
	->withPaths([
		__DIR__ . '/appinfo',
		__DIR__ . '/lib',
		__DIR__ . '/tests',
	])
	->withPhpSets(php81: true)
	->withTypeCoverageLevel(0)
	->withSets([
		NextcloudSets::NEXTCLOUD_30,
	]);
```

Make sure that you also have nextcloud/coding-standard setup and to run the code style fixer after rector to fix styling.


## Contributing

Contributions are welcome! To contribute, please familiarize yourself with
[CONTRIBUTING.md](CONTRIBUTING.md).

## Copyright and License

nextcloud/rector is copyright © [Christoph Wurst](https://wuc.me)
and licensed for use under the terms of the
GNU Affero General Public License (AGPL-3.0-or-later) as published by the Free
Software Foundation. Please see [COPYING](COPYING) and [NOTICE](NOTICE) for more
information.
