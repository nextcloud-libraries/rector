<?php

declare(strict_types=1);

use Nextcloud\Rector\Rector\LegacyGetterToOcpServerGetRector;
use Nextcloud\Rector\Rector\OcpUtilAddScriptRector;
use Nextcloud\Rector\ValueObject\LegacyGetterToOcpServerGet;
use Rector\Config\RectorConfig;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rules([
        OcpUtilAddScriptRector::class,
    ]);
    $rectorConfig->ruleWithConfiguration(
        LegacyGetterToOcpServerGetRector::class,
        [
            // Deprecated since 20.0.0
            new LegacyGetterToOcpServerGet('getCalendarManager', 'OCP\Calendar\IManager'),
            // Deprecated since 20.0.0
            new LegacyGetterToOcpServerGet('getCalendarResourceBackendManager', 'OCP\Calendar\Resource\IManager'),
            // Deprecated since 20.0.0
            new LegacyGetterToOcpServerGet('getCalendarRoomBackendManager', 'OCP\Calendar\Room\IManager'),
            // Deprecated since 20.0.0
            new LegacyGetterToOcpServerGet('getContactsManager', 'OCP\Contacts\IManager'),
            // Deprecated since 20.0.0
            new LegacyGetterToOcpServerGet('getEncryptionManager', 'OCP\Encryption\IManager'),
            // Deprecated since 20.0.0
            new LegacyGetterToOcpServerGet('getEncryptionFilesHelper', 'OCP\Encryption\IFile'),
            // Deprecated since 20.0.0
            new LegacyGetterToOcpServerGet('getEncryptionKeyStorage', 'OCP\Encryption\Keys\IStorage'),
            // Deprecated since 20.0.0
            new LegacyGetterToOcpServerGet('getRequest', 'OCP\IRequest'),
            // Deprecated since 20.0.0
            new LegacyGetterToOcpServerGet('getPreviewManager', 'OCP\IPreview'),
            // Deprecated since 20.0.0
            new LegacyGetterToOcpServerGet('getTagManager', 'OCP\ITagManager'),
            // Deprecated since 20.0.0
            new LegacyGetterToOcpServerGet('getSystemTagManager', 'OCP\SystemTag\ISystemTagManager'),
            // Deprecated since 20.0.0
            new LegacyGetterToOcpServerGet('getSystemTagObjectMapper', 'OCP\SystemTag\ISystemTagObjectMapper'),
            // Deprecated since 20.0.0
            new LegacyGetterToOcpServerGet('getAvatarManager', 'OCP\IAvatarManager'),
            // Deprecated since 20.0.0
            new LegacyGetterToOcpServerGet('getRootFolder', 'OCP\Files\IRootFolder'),
            // Deprecated since 20.0.0
            new LegacyGetterToOcpServerGet('getLazyRootFolder', 'OCP\Files\IRootFolder'),
            // Deprecated since 20.0.0
            new LegacyGetterToOcpServerGet('getUserManager', 'OCP\User\IManager'),
            // Deprecated since 20.0.0
            new LegacyGetterToOcpServerGet('getGroupManager', 'OCP\Group\IManager'),
            // Deprecated since 20.0.0
            new LegacyGetterToOcpServerGet('getUserSession', 'OCP\IUserSession'),
            // Deprecated since 20.0.0
            new LegacyGetterToOcpServerGet('getSession', 'OCP\ISession'),
            // Deprecated since 20.0.0
            new LegacyGetterToOcpServerGet('getTwoFactorAuthManager', 'OC\Authentication\TwoFactorAuth\Manager'),
            // Deprecated since 20.0.0
            new LegacyGetterToOcpServerGet('getNavigationManager', 'OCP\INavigationManager'),
            // Deprecated since 20.0.0
            new LegacyGetterToOcpServerGet('getConfig', 'OCP\IConfig'),
            // Deprecated since 20.0.0
            new LegacyGetterToOcpServerGet('getSystemConfig', 'OC\SystemConfig'),
            // Deprecated since 20.0.0
            new LegacyGetterToOcpServerGet('getAppConfig', 'OCP\IAppConfig'),
            // Deprecated since 20.0.0
            new LegacyGetterToOcpServerGet('getURLGenerator', 'OCP\IURLGenerator'),
            // Deprecated since 20.0.0
            new LegacyGetterToOcpServerGet('getAppFetcher', 'OC\App\AppStore\Fetcher\AppFetcher'),
            // Deprecated since 20.0.0
            new LegacyGetterToOcpServerGet('getMemCacheFactory', 'OCP\ICacheFactory'),
            // Deprecated since 20.0.0
            new LegacyGetterToOcpServerGet('getGetRedisFactory', 'OC\RedisFactory'),
            // Deprecated since 20.0.0
            new LegacyGetterToOcpServerGet('getDatabaseConnection', 'OCP\IDBConnection'),
            // Deprecated since 20.0.0
            new LegacyGetterToOcpServerGet('getActivityManager', 'OCP\Activity\IManager'),
            // Deprecated since 20.0.0
            new LegacyGetterToOcpServerGet('getJobList', 'OCP\BackgroundJob\IJobList'),
            // Deprecated since 20.0.0
            new LegacyGetterToOcpServerGet('getLogFactory', 'OCP\Log\ILogFactory'),
            // Deprecated since 20.0.0
            new LegacyGetterToOcpServerGet('getRouter', 'OCP\Route\IRouter'),
            // Deprecated since 20.0.0
            new LegacyGetterToOcpServerGet('getSecureRandom', 'OCP\Security\ISecureRandom'),
            // Deprecated since 20.0.0
            new LegacyGetterToOcpServerGet('getCrypto', 'OCP\Security\ICrypto'),
            // Deprecated since 20.0.0
            new LegacyGetterToOcpServerGet('getHasher', 'OCP\Security\IHasher'),
            // Deprecated since 20.0.0
            new LegacyGetterToOcpServerGet('getCredentialsManager', 'OCP\Security\ICredentialsManager'),
            // Deprecated since 20.0.0
            new LegacyGetterToOcpServerGet('getHTTPClientService', 'OCP\Http\Client\IClientService'),
            // Deprecated since 20.0.0
            new LegacyGetterToOcpServerGet('getEventLogger', 'OCP\Diagnostics\IEventLogger'),
            // Deprecated since 20.0.0
            new LegacyGetterToOcpServerGet('getQueryLogger', 'OCP\Diagnostics\IQueryLogger'),
            // Deprecated since 20.0.0
            new LegacyGetterToOcpServerGet('getTempManager', 'OCP\ITempManager'),
            // Deprecated since 20.0.0
            new LegacyGetterToOcpServerGet('getAppManager', 'OCP\App\IAppManager'),
            // Deprecated since 20.0.0
            new LegacyGetterToOcpServerGet('getMailer', 'OCP\Mail\IMailer'),
            // Deprecated since 20.0.0
            new LegacyGetterToOcpServerGet('getDateTimeZone', 'OCP\IDateTimeZone'),
            // Deprecated since 20.0.0
            new LegacyGetterToOcpServerGet('getDateTimeFormatter', 'OCP\IDateTimeFormatter'),
            // Deprecated since 20.0.0
            new LegacyGetterToOcpServerGet('getMountProviderCollection', 'OCP\Files\Config\IMountProviderCollection'),
            // Deprecated since 20.0.0
            new LegacyGetterToOcpServerGet('getIniWrapper', 'bantu\IniGetWrapper\IniGetWrapper'),
            // Deprecated since 20.0.0
            new LegacyGetterToOcpServerGet('getCommandBus', 'OCP\Command\IBus'),
            // Deprecated since 20.0.0
            new LegacyGetterToOcpServerGet('getTrustedDomainHelper', 'OCP\Security\ITrustedDomainHelper'),
            // Deprecated since 20.0.0
            new LegacyGetterToOcpServerGet('getLockingProvider', 'OCP\Lock\ILockingProvider'),
            // Deprecated since 20.0.0
            new LegacyGetterToOcpServerGet('getMountManager', 'OCP\Files\Mount\IMountManager'),
            // Deprecated since 20.0.0
            new LegacyGetterToOcpServerGet('getUserMountCache', 'OCP\Files\Config\IUserMountCache'),
            // Deprecated since 20.0.0
            new LegacyGetterToOcpServerGet('getMimeTypeDetector', 'OCP\Files\IMimeTypeDetector'),
            // Deprecated since 20.0.0
            new LegacyGetterToOcpServerGet('getMimeTypeLoader', 'OCP\Files\IMimeTypeLoader'),
            // Deprecated since 20.0.0
            new LegacyGetterToOcpServerGet('getNotificationManager', 'OCP\Notification\IManager'),
            // Deprecated since 20.0.0
            new LegacyGetterToOcpServerGet('getCommentsManager', 'OCP\Comments\ICommentsManager'),
            // Deprecated since 20.0.0
            new LegacyGetterToOcpServerGet('getThemingDefaults', 'OCA\Theming\ThemingDefaults'),
            // Deprecated since 20.0.0
            new LegacyGetterToOcpServerGet('getIntegrityCodeChecker', 'OC\IntegrityCheck\Checker'),
            // Deprecated since 20.0.0
            new LegacyGetterToOcpServerGet('getSessionCryptoWrapper', 'OC\Session\CryptoWrapper'),
            // Deprecated since 20.0.0
            new LegacyGetterToOcpServerGet('getCsrfTokenManager', 'OC\Security\CSRF\CsrfTokenManager'),
            // Deprecated since 20.0.0
            new LegacyGetterToOcpServerGet('getBruteForceThrottler', 'OCP\Security\Bruteforce\IThrottler'),
            // Deprecated since 20.0.0
            new LegacyGetterToOcpServerGet(
                'getContentSecurityPolicyManager',
                'OCP\Security\IContentSecurityPolicyManager',
            ),
            // Deprecated since 20.0.0
            new LegacyGetterToOcpServerGet(
                'getContentSecurityPolicyNonceManager',
                'OC\Security\CSP\ContentSecurityPolicyNonceManager',
            ),
            // Deprecated since 20.0.0
            new LegacyGetterToOcpServerGet('getStoragesBackendService', 'OCA\Files_External\Service\BackendService'),
            // Deprecated since 20.0.0
            new LegacyGetterToOcpServerGet(
                'getGlobalStoragesService',
                'OCA\Files_External\Service\GlobalStoragesService',
            ),
            // Deprecated since 20.0.0
            new LegacyGetterToOcpServerGet(
                'getUserGlobalStoragesService',
                'OCA\Files_External\Service\UserGlobalStoragesService',
            ),
            // Deprecated since 20.0.0
            new LegacyGetterToOcpServerGet('getUserStoragesService', 'OCA\Files_External\Service\UserStoragesService'),
            // Deprecated since 20.0.0
            new LegacyGetterToOcpServerGet('getShareManager', 'OCP\Share\IManager'),
            // Deprecated since 20.0.0
            new LegacyGetterToOcpServerGet('getCollaboratorSearch', 'OCP\Collaboration\Collaborators\ISearch'),
            // Deprecated since 20.0.0
            new LegacyGetterToOcpServerGet('getAutoCompleteManager', 'OCP\Collaboration\AutoComplete\IManager'),
            // Deprecated since 20.0.0
            new LegacyGetterToOcpServerGet('getLDAPProvider', 'OCP\LDAP\ILDAPProvider'),
            // Deprecated since 20.0.0
            new LegacyGetterToOcpServerGet('getSettingsManager', 'OCP\Settings\IManager'),
            // Deprecated since 20.0.0 Use 'get(\OCP\Files\AppData\IAppDataFactory')->get($app) instead
            new LegacyGetterToOcpServerGet('getAppDataDir', 'OCP\Files\IAppData'),
            // Deprecated since 20.0.0
            new LegacyGetterToOcpServerGet('getLockdownManager', 'OCP\Lockdown\ILockdownManager'),
            // Deprecated since 20.0.0
            new LegacyGetterToOcpServerGet('getCloudIdManager', 'OCP\Federation\ICloudIdManager'),
            // Deprecated since 20.0.0
            new LegacyGetterToOcpServerGet('getGlobalScaleConfig', 'OCP\GlobalScale\IConfig'),
            // Deprecated since 20.0.0
            new LegacyGetterToOcpServerGet(
                'getCloudFederationProviderManager',
                'OCP\Federation\ICloudFederationProviderManager',
            ),
            // Deprecated since 20.0.0
            new LegacyGetterToOcpServerGet('getRemoteApiFactory', 'OCP\Remote\Api\IApiFactory'),
            // Deprecated since 20.0.0
            new LegacyGetterToOcpServerGet('getCloudFederationFactory', 'OCP\Federation\ICloudFederationFactory'),
            // Deprecated since 20.0.0
            new LegacyGetterToOcpServerGet('getRemoteInstanceFactory', 'OCP\Remote\IInstanceFactory'),
            // Deprecated since 20.0.0
            new LegacyGetterToOcpServerGet('getStorageFactory', 'OCP\Files\Storage\IStorageFactory'),
            // Deprecated since 20.0.0
            new LegacyGetterToOcpServerGet('getGeneratorHelper', 'OC\Preview\GeneratorHelper'),
        ],
    );
};
