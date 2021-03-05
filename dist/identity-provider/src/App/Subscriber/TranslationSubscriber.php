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

namespace Sugarcrm\IdentityProvider\App\Subscriber;

use Sugarcrm\IdentityProvider\App\Application;
use Sugarcrm\IdentityProvider\App\Provider\TenantConfigInitializer;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class TranslationSubscriber implements EventSubscriberInterface
{
    /**
     * @var Application
     */
    protected $app;

    /**
     * @var string
     */
    protected $locale;

    /**
     * @var string
     */
    protected $localeParamName;

    public function __construct(Application $app, $localeParamName)
    {
        $this->app = $app;
        $this->locale =  $this->app['locale'];
        $this->localeParamName = $localeParamName;
    }

    /**
     * On kernel request subscriber
     * @param GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        $this->app['selectedUserLocale'] = null;
        $request = $event->getRequest();
        switch (true) {
            case $request->query->has($this->localeParamName):
                $this->locale = $request->query->get($this->localeParamName);
                $this->app['selectedUserLocale'] = $this->locale;
                break;
            case !empty($this->app->getCookieService()->getLocaleCookie($request)):
                $this->locale = $this->app->getCookieService()->getLocaleCookie($request);
                $this->app['selectedUserLocale'] = $this->locale;
                break;
        }

        $session = $this->app->getSession();
        $tenant = $session->get('tenant') ?? $request->query->get(TenantConfigInitializer::REQUEST_KEY);

        if ($tenant) {
            $metadataService = $this->app->getMetadataService();
            $defaultLanguage = $metadataService->getDefaultLanguage($tenant);
            $availableLanguages = $metadataService->getLanguages($tenant);

            if ($defaultLanguage) {
                if (!$this->app['selectedUserLocale']) {
                    $this->locale = $defaultLanguage;
                } elseif (!array_key_exists($this->app['selectedUserLocale'], $availableLanguages)) {
                    $this->locale = $defaultLanguage;
                    $this->app['selectedUserLocale'] = $defaultLanguage;
                }
            }
        }

        $this->app['locale'] = $this->locale;
    }

    /**
     * On kernel response subscriber
     * @param FilterResponseEvent $event
     */
    public function onKernelResponse(FilterResponseEvent $event)
    {
        $response = $event->getResponse();
        $cookieService = $this->app->getCookieService();
        if ($this->app['selectedUserLocale']) {
            $cookieService->setLocaleCookie($response, $this->app['selectedUserLocale']);
        }

        $cookieService->setUICookie($response, $this->app['locale']);
    }

    /**
     * @inheritdoc
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => [['onKernelRequest', 9]],
            KernelEvents::RESPONSE => [['onKernelResponse', 0]],
        ];
    }
}
