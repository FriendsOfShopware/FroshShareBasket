<?php

namespace FroshShareBasket\Subscriber;

use Enlight\Event\SubscriberInterface;

class Analytics implements SubscriberInterface
{
    /**
     * @var string
     */
    private $pluginDir;

    /**
     * Analytics constructor.
     *
     * @param string $pluginDir
     */
    public function __construct($pluginDir)
    {
        $this->pluginDir = $pluginDir;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            'Enlight_Controller_Action_PostDispatchSecure_Backend_Analytics' => 'onPostDispatchBackendAnalytics',
        ];
    }

    /**
     * @param \Enlight_Controller_ActionEventArgs $args
     */
    public function onPostDispatchBackendAnalytics(\Enlight_Controller_ActionEventArgs $args)
    {
        $request = $args->getRequest();
        $view = $args->getSubject()->View();

        $view->addTemplateDir($this->pluginDir . '/Resources/views/');

        if ($request->getActionName() === 'index') {
            $view->extendsTemplate('backend/analytics/frosh_share_basket/app.js');
        }

        if ($request->getActionName() === 'load') {
            $view->extendsTemplate('backend/analytics/frosh_share_basket/store/navigation.js');
        }
    }
}
