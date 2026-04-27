<?php

declare(strict_types=1);

/*
 * SPDX-FileCopyrightText: 2026 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace Nextcloud\Rector\Rector;

use Override;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

final class ReplaceIConfigWithIUserConfigRector extends AReplaceClassRector
{
    #[Override]
    public function getOldClassName(): string
    {
        return 'OCP\IConfig';
    }

    #[Override]
    public function getNewClassName(): string
    {
        return 'OCP\IUserConfig';
    }

    #[Override]
    public function getDesiredVarName(): string
    {
        return 'userConfig';
    }

    #[Override]
    public function getMethodMap(): array
    {
        return [
            'getAllUserValues' => 'getAllValues',
            'getUserKeys' => 'getKeys',
            'getUserValue' => 'getValueString',
            'getUserValueForUsers' => 'getValuesByUsers',
            'getUsersForUserValue' => 'searchUsersByValueString',
            'setUserValue' => 'setValueString',
            'deleteUserValue' => 'deleteUserConfig',
            'deleteAllUserValues' => 'deleteAllUserConfig',
            'deleteAppFromAllUsers' => 'deleteApp',
        ];
    }

    #[Override]
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Replace deprecated \OCP\IConfig user-config methods with their \OCP\IUserConfig counterparts,'
            . ' injecting IUserConfig alongside the existing IConfig.',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
use OCP\IConfig;

class SomeClass
{
    public function __construct(private IConfig $config) {}

    public function run(): string
    {
        return $this->config->getUserValue('user', 'myapp', 'mykey', 'default');
    }
}
CODE_SAMPLE,
                    <<<'CODE_SAMPLE'
use OCP\IConfig;
use OCP\IUserConfig;

class SomeClass
{
    public function __construct(private IConfig $config, private IUserConfig $userConfig) {}

    public function run(): string
    {
        return $this->userConfig->getValueString('user', 'myapp', 'mykey', 'default');
    }
}
CODE_SAMPLE,
                ),
            ],
        );
    }
}
