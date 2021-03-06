<?php
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

namespace Sugarcrm\IdentityProvider\App;

use Doctrine\DBAL\Connection;

use Monolog\Logger;
use Monolog\Handler\ErrorLogHandler;
use Monolog\Processor\UidProcessor;
use Monolog\Processor\WebProcessor;
use Monolog\Processor\IntrospectionProcessor;
use Monolog\Processor\PsrLogMessageProcessor;

use Psr\Log\LoggerInterface;

use Silex\Application as SilexApplication;
use Silex\Provider\DoctrineServiceProvider;
use Silex\Provider\MonologServiceProvider;
use Silex\Provider\SessionServiceProvider;
use Silex\Provider\TwigServiceProvider;
use Silex\Provider\ValidatorServiceProvider;
use Silex\Provider\AssetServiceProvider;
use Silex\Provider\CsrfServiceProvider;
use Sugarcrm\IdentityProvider\App\Authentication\OIDCExternalService;
use Sugarcrm\IdentityProvider\App\Mango;
use Sugarcrm\IdentityProvider\App\Provider\MetadataProvider;
use Sugarcrm\IdentityProvider\App\Provider\OIDCExternalServiceProvider;
use Symfony\Component\HttpFoundation\Request;

use Sugarcrm\Apis\Iam\App\V1alpha\AppAPIClient;
use Sugarcrm\Apis\Iam\Consent\V1alpha\ConsentAPIClient;
use Sugarcrm\Apis\Iam\User\V1alpha\UserAPIClient;
use Sugarcrm\IdentityProvider\App\Authentication\BearerAuthentication;
use Sugarcrm\IdentityProvider\App\Authentication\ConfigAdapter\ConfigAdapterFactory;
use Sugarcrm\IdentityProvider\App\Authentication\CookieService;
use Sugarcrm\IdentityProvider\App\Authentication\LogoutService;
use Sugarcrm\IdentityProvider\App\Authentication\OpenId\StandardClaimsService;
use Sugarcrm\IdentityProvider\App\Authentication\RedirectURLService;
use Sugarcrm\IdentityProvider\App\Authentication\RevokeAccessTokensService;
use Sugarcrm\IdentityProvider\App\Authentication\UserProvider\UserProviderBuilder;
use Sugarcrm\IdentityProvider\App\Grpc\AppService;
use Sugarcrm\IdentityProvider\App\MarketingExtras\MarketingExtrasService;
use Sugarcrm\IdentityProvider\App\Provider\BearerAuthenticationProvider;
use Sugarcrm\IdentityProvider\App\Provider\ConfigAdapterFactoryServiceProvider;
use Sugarcrm\IdentityProvider\App\Provider\CookieServiceProvider;
use Sugarcrm\IdentityProvider\App\Provider\EncoderFactoryProvider;
use Sugarcrm\IdentityProvider\App\Provider\GrpcServiceProvider;
use Sugarcrm\IdentityProvider\App\Provider\MarketingExtrasServiceProvider;
use Sugarcrm\IdentityProvider\App\Provider\TranslationServiceProvider;
use Sugarcrm\IdentityProvider\App\Provider\UserPasswordCheckerProvider;
use Sugarcrm\IdentityProvider\App\User\PasswordChecker;
use Sugarcrm\IdentityProvider\App\Regions\TenantRegion;
use Sugarcrm\IdentityProvider\App\Provider\AppServiceProvider;
use Sugarcrm\IdentityProvider\App\Provider\AuditProvider;
use Sugarcrm\IdentityProvider\App\Provider\LogoutServiceProvider;
use Sugarcrm\IdentityProvider\App\Provider\RedirectURLServiceProvider;
use Sugarcrm\IdentityProvider\App\Provider\RepositoriesProvider;
use Sugarcrm\IdentityProvider\App\Provider\RevokeAccessTokensServiceProvider;
use Sugarcrm\IdentityProvider\App\Provider\JoseServiceProvider;
use Sugarcrm\IdentityProvider\App\Provider\ListenerProvider;
use Sugarcrm\IdentityProvider\App\Provider\OAuth2ServiceProvider;
use Sugarcrm\IdentityProvider\App\Provider\AuthProviderManagerProvider;
use Sugarcrm\IdentityProvider\App\Provider\ConfigServiceProvider;
use Sugarcrm\IdentityProvider\App\Provider\TenantRegionProvider;
use Sugarcrm\IdentityProvider\App\Provider\SrnManagerServiceProvider;
use Sugarcrm\IdentityProvider\App\Provider\UserMappingServiceProvider;
use Sugarcrm\IdentityProvider\App\Provider\RememberMeServiceProvider;
use Sugarcrm\IdentityProvider\App\Provider\TenantConfigurationServiceProvider;
use Sugarcrm\IdentityProvider\App\Provider\ConsentRequestProvider;
use Sugarcrm\IdentityProvider\App\Provider\UsernamePasswordTokenFactoryProvider;
use Sugarcrm\IdentityProvider\App\Provider\ErrorPageHandlerProvider;
use Sugarcrm\IdentityProvider\App\Provider\OIDCClaimsServiceProvider;
use Sugarcrm\IdentityProvider\App\Provider\RegionCheckerProvider;
use Sugarcrm\IdentityProvider\App\Provider\NewRelicProfilerProvider;
use Sugarcrm\IdentityProvider\App\Instrumentation\NewRelicProfiler;
use Sugarcrm\IdentityProvider\App\Repository\ConsentRepository;
use Sugarcrm\IdentityProvider\App\Repository\OneTimeTokenRepository;
use Sugarcrm\IdentityProvider\App\Repository\TenantRepository;
use Sugarcrm\IdentityProvider\App\Repository\UserProvidersRepository;
use Sugarcrm\IdentityProvider\App\Repository\UserRepository;
use Sugarcrm\IdentityProvider\Authentication\Audit;
use Sugarcrm\IdentityProvider\Authentication\Token\UsernamePasswordTokenFactory;
use Sugarcrm\IdentityProvider\Authentication\UserMapping\MappingInterface;
use Sugarcrm\IdentityProvider\Srn\Manager;
use Sugarcrm\IdentityProvider\App\Instrumentation;
use Sugarcrm\IdentityProvider\App\Provider\UserProviderBuilderProvider;
use Sugarcrm\IdentityProvider\App\Regions\RegionChecker;

use Symfony\Component\Translation\Translator;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\PdoSessionHandler;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Security\Core\Authentication\AuthenticationProviderManager;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Validator\Validator\RecursiveValidator;

class Application extends SilexApplication
{
    // Cookie or query parameter name
    const LOCALE_PARAM_NAME = 'ulcid';

    const ENV_PROD = 'prod';
    const ENV_DEV = 'dev';
    const ENV_TESTS = 'tests';
    const ENV_DEFAULT = self::ENV_PROD;

    /**
     * Prometheus metrics endpoint
     * @var string
     */
    const METRICS_ENDPOINT = '/metrics';

    /**
     * @var string
     */
    protected $env;

    /**
     * @var string
     */
    protected $rootDir;

    /**
     * Allowed environments
     * @var array
     */
    protected $allowedEnv = [
        self::ENV_TESTS,
        self::ENV_DEV,
        self::ENV_PROD,
    ];

    /**
     * @inheritdoc
     */
    public function __construct(array $values = ['env' => self::ENV_DEFAULT])
    {
        $environment = (string) $values['env'];
        $this->env = in_array($environment, $this->allowedEnv) ? $environment : self::ENV_DEFAULT;

        $this->rootDir = realpath(__DIR__ . '/../../');

        parent::__construct();

        $this->register(new ConfigServiceProvider(isset($values['configOverride']) ? $values['configOverride'] : []));

        $this->register(new TranslationServiceProvider($this['config']['translation'], self::LOCALE_PARAM_NAME));
        $this->register(new TenantRegionProvider());

        $this->register(new MonologServiceProvider(), $this['config']['monolog']);
        $this->extend('monolog', function (Logger $monolog, Application $app) {
            return $monolog->pushProcessor(new UidProcessor())
                ->pushProcessor(new WebProcessor())
                ->pushProcessor(new IntrospectionProcessor())
                ->pushProcessor(new PsrLogMessageProcessor())
                ->pushHandler(
                    new ErrorLogHandler(
                        ErrorLogHandler::OPERATING_SYSTEM,
                        $app['config']['monolog']['monolog.level'],
                        $app['config']['monolog']['monolog.bubble']
                    )
                );
        });

        $this->register(new AssetServiceProvider(), [
            'assets.version' => 'v6',
            'assets.version_format' => '%s?version=%s',
            'assets.named_packages' => [
                'css' => ['base_path' => 'css', 'version' => 'v10', 'version_format' => '%s?version=%s'],
                'js' => ['base_path' => 'js', 'version' => 'v5', 'version_format' => '%s?version=%s'],
                'images' => ['base_path' => 'img', 'version' => 'v1', 'version_format' => '%s?version=%s'],
            ],
        ]);

        $this->register(new TwigServiceProvider(), [
            'twig.options' => array_replace([
                'cache' => $this->rootDir . '/var/cache/twig',
                'strict_variables' => true,
            ], $this['config']['twig']['twig.options'] ?? []),
            'twig.path' => __DIR__ . '/Resources/views',
        ]);

        $this->extend('twig', function (\Twig\Environment $twig, $app) {
            $twig->addExtension(new Twig\Extension($app));
            return $twig;
        });

        $this->register(new ValidatorServiceProvider());
        $this->register(new RegionCheckerProvider());

        $this->register(new DoctrineServiceProvider(), $this['config']['db']);
        $this->register(new RepositoriesProvider());

        // Should be before TenantConfigurationServiceProvider
        $this->register(new ConfigAdapterFactoryServiceProvider());

        // Should be before:
        //  AuthProviderManagerProvider, UserMappingServiceProvider, UsernamePasswordTokenFactoryProvider
        // Add after DoctrineServiceProvider
        $this->register(new TenantConfigurationServiceProvider());

        $this->register(new SessionServiceProvider(), [
            'session.test' => $environment === self::ENV_TESTS,
            'session.storage.options' => $this['config']['session.storage.options'],
        ]);

        $this['session.storage.handler'] = function () {
            return new PdoSessionHandler(
                $this['db']->getWrappedConnection(),
                [
                    'db_table' => 'sessions',
                    'db_id_col' => 'session_id',
                    'db_data_col' => 'session_value',
                    'db_lifetime_col' => 'session_lifetime',
                    'db_time_col' => 'session_time',
                    'lock_mode' => PdoSessionHandler::LOCK_ADVISORY,
                ]
            );
        };

        $this->register(new AuthProviderManagerProvider());
        $this->register(new UserMappingServiceProvider());
        $this->register(new JoseServiceProvider());
        $this->register(new OAuth2ServiceProvider());
        $this->register(new ConsentRequestProvider());
        $this->register(new UsernamePasswordTokenFactoryProvider());
        $this->register(new SrnManagerServiceProvider());
        $this->register(new CsrfServiceProvider());
        $this->register(new ErrorPageHandlerProvider());
        $this->register(new RememberMeServiceProvider());
        $this->register(new ListenerProvider());
        $this->register(new EncoderFactoryProvider());
        $this->register(new GrpcServiceProvider());
        $this->register(new LogoutServiceProvider());
        $this->register(new OIDCClaimsServiceProvider());
        $this->register(new OIDCExternalServiceProvider());
        $this->register(new UserPasswordCheckerProvider());
        $this->register(new MarketingExtrasServiceProvider());
        $this->register(new AppServiceProvider());
        $this->register(new RevokeAccessTokensServiceProvider());
        $this->register(new BearerAuthenticationProvider());
        $this->register(new CookieServiceProvider(self::LOCALE_PARAM_NAME));
        $this->register(new RedirectURLServiceProvider());
        $this->register(new AuditProvider());
        $this->register(new NewRelicProfilerProvider());
        $this->register(new MetadataProvider());
        $this->register(new UserProviderBuilderProvider());

        // bind routes
        $this->mount('', new ControllerProvider());
        if ($environment != self::ENV_TESTS) {
            // instrumentation
            $prometheusMetrics = new Instrumentation\PrometheusMetrics(self::METRICS_ENDPOINT);
            $prometheusMetrics->initialize($this);
            $this->get(self::METRICS_ENDPOINT, $prometheusMetrics->render());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function run(Request $request = null)
    {
        $this->getNewRelicProfiler()->start();
        parent::run($request);
        $this->getNewRelicProfiler()->stop();
    }

    /**
     * @return string
     */
    public function getEnv()
    {
        return $this->env;
    }

    /**
     * @return string
     */
    public function getRootDir()
    {
        return $this->rootDir;
    }

    /**
     * SERVICE ACCESSORS
     */

    /**
     * @return \Twig_Environment
     */
    public function getTwigService()
    {
        return $this['twig'];
    }

    /**
     * @return RecursiveValidator
     */
    public function getValidatorService()
    {
        return $this['validator'];
    }

    /**
     * @return Connection
     */
    public function getDoctrineService()
    {
        return $this['db'];
    }

    /**
     * @return AuthenticationProviderManager
     */
    public function getAuthManagerService()
    {
        return $this['authManager'];
    }

    /**
     * @return UrlGenerator
     */
    public function getUrlGeneratorService()
    {
        return $this['url_generator'];
    }

    /**
     * @return LoggerInterface;
     */
    public function getLogger()
    {
        return $this['logger'];
    }

    /**
     * @return Session
     */
    public function getSession()
    {
        return $this['session'];
    }

    /**
     * @param string $type Type of the mapping service (ldap, saml).
     *
     * @return MappingInterface
     */
    public function getUserMappingService($type)
    {
        $mappingServiceName = strtoupper($type) . 'UserMapping';
        if (empty($this[$mappingServiceName])) {
            throw new \InvalidArgumentException("Requested mapping service $mappingServiceName is missing");
        }
        return $this[$mappingServiceName]();
    }

    /**
     * @param string $username
     * @param string $password
     * @return UsernamePasswordTokenFactory
     */
    public function getUsernamePasswordTokenFactory($username, $password)
    {
        return $this['usernamePasswordTokenFactory']($username, $password);
    }

    /**
     * @return ConsentRepository
     */
    public function getConsentRepository(): ConsentRepository
    {
        return $this['consentRepository'];
    }

    /**
     * @return TenantRepository
     */
    public function getTenantRepository(): TenantRepository
    {
        return $this['tenantRepository'];
    }

    public function getOneTimeTokenRepository(): OneTimeTokenRepository
    {
        return $this['oneTimeTokenRepository'];
    }

    /**
     * @return UserProvidersRepository
     */
    public function getUserProvidersRepository(): UserProvidersRepository
    {
        return $this['userProvidersRepository'];
    }

    /**
     * @return UserRepository
     */
    public function getUserRepository(): UserRepository
    {
        return $this['userRepository'];
    }

    /**
     * @return TenantConfiguration
     */
    public function getTenantConfiguration()
    {
        return $this['tenantConfiguration'];
    }

    /**
     * @return ConfigAdapterFactory
     */
    public function getConfigAdapterFactory()
    {
        return $this['configAdapterFactory'];
    }

    /**
     * @param string $region
     * @return Manager
     */
    public function getSrnManager(string $region): Manager
    {
        return $this['SrnManager']($region);
    }

    /**
     * @return \Sugarcrm\IdentityProvider\App\Authentication\OAuth2Service
     */
    public function getOAuth2Service()
    {
        return $this['oAuth2Service'];
    }

    /**
     * @return \Sugarcrm\IdentityProvider\App\Authentication\JoseService
     */
    public function getJoseService()
    {
        return $this['JoseService'];
    }

    /**
     * @return \Sugarcrm\IdentityProvider\App\Authentication\ConsentRequest\ConsentRestService
     */
    public function getConsentRestService()
    {
        return $this['consentRestService'];
    }

    /**
     * @return \Symfony\Component\Security\Csrf\CsrfTokenManager
     */
    public function getCsrfTokenManager()
    {
        return $this['csrf.token_manager'];
    }

    /**
     * @return \Sugarcrm\IdentityProvider\App\Authentication\RememberMe\Service
     */
    public function getRememberMeService()
    {
        return $this['RememberMe'];
    }

    /**
     * @return EventDispatcher
     */
    public function getEventDispatcher()
    {
        return $this['dispatcher'];
    }

    /**
     * Get Application config.
     *
     * @return array
     */
    public function getConfig(): array
    {
        return $this['config'] ?? [];
    }

    /**
     * @return EncoderFactoryInterface
     */
    public function getEncoderFactory(): EncoderFactoryInterface
    {
        return $this['encoderFactory'];
    }

    /**
     * Get ServiceDiscovery.
     *
     * @return ServiceDiscovery|null
     */
    public function getServiceDiscovery(): ?ServiceDiscovery
    {
        return $this['grpc.discovery'];
    }

    /**
     * @return UserAPIClient|null
     */
    public function getGrpcUserApi(): ?UserAPIClient
    {
        return $this['grpc.userapi'];
    }

    /**
     * @return AppAPIClient|null
     */
    public function getGrpcAppApi(): ?AppAPIClient
    {
        return $this['grpc.appapi'];
    }

    /**
     * @return ConsentAPIClient|null
     */
    public function getGrpcConsentApi(): ?ConsentAPIClient
    {
        return $this['grpc.consentapi'];
    }

    /**
     * @return RevokeAccessTokensService|null
     */
    public function getRevokeAccessTokensService(): ?RevokeAccessTokensService
    {
        return $this['revokeAccessTokensService'];
    }

    /**
     * @return LogoutService
     */
    public function getLogoutService(): LogoutService
    {
        return $this['logout'];
    }

    /**
     * @return RedirectURLService
     */
    public function getRedirectURLService(): RedirectURLService
    {
        return $this['redirectURLService'];
    }

    /**
     * @return Translator
     */
    public function getTranslator(): Translator
    {
        return $this['translator'];
    }

    /**
     * @return StandardClaimsService
     */
    public function getOIDCClaimsService(): StandardClaimsService
    {
        return $this['OIDCClaimsService'];
    }

    /**
     * @return PasswordChecker
     */
    public function getUserPasswordChecker(): PasswordChecker
    {
        return $this['userPasswordChecker'];
    }

    /**
     * @return MarketingExtrasService
     */
    public function getMarketingExtrasService(): MarketingExtrasService
    {
        return $this['marketingExtras'];
    }

    /**
     * @return BearerAuthentication
     */
    public function getBearerAuthentication(): BearerAuthentication
    {
        return $this['bearerAuthentication'];
    }

    /**
     * @return CookieService
     */
    public function getCookieService(): CookieService
    {
        return $this['cookies'];
    }

    /**
     * @return TenantRegion
     */
    public function getTenantRegion(): TenantRegion
    {
        return $this['TenantRegion'];
    }

    /**
     * @return RegionChecker
     */
    public function getRegionChecker(): RegionChecker
    {
        return $this['RegionChecker'];
    }

    /**
     * @return Audit
     */
    public function getAudit(): Audit
    {
        return $this['audit'];
    }

    /**
     * @return NewRelicProfiler
     */
    public function getNewRelicProfiler(): NewRelicProfiler
    {
        return $this['newRelicProfiler'];
    }

    /**
     * @return Mango\MetadataService
     */
    public function getMetadataService(): Mango\MetadataService
    {
        return $this['metadata'];
    }

    /**
     * @return Mango\RestService
     */
    public function getMangoRestService(): Mango\RestService
    {
        return $this['mangoRestService'];
    }

    /**
     * @return AppService
     */
    public function getGrpcAppService(): AppService
    {
        return $this['grpcAppService'];
    }

    /**
     * @return OIDCExternalService
     */
    public function getOIDCExternalService(): OIDCExternalService
    {
        return $this['OIDCExternalService'];
    }

    /**
     * @return UserProviderBuilder
     */
    public function getUserProviderBuilder(): UserProviderBuilder
    {
        return $this['userProviderBuilder'];
    }
}
