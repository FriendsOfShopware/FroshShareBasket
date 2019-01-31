<?php

namespace FroshShareBasket\Subscriber;

use Enlight\Event\SubscriberInterface;
use Shopware\Components\DependencyInjection\Container as DIContainer;

class Checkout implements SubscriberInterface
{
    /**
     * @var DIContainer
     */
    private $container;

    /**
     * Checkout constructor.
     *
     * @param DIContainer $container
     */
    public function __construct(DIContainer $container)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            'Enlight_Controller_Action_PostDispatchSecure_Frontend_Checkout' => 'onPostDispatchCheckout',
        ];
    }

    public function onPostDispatchCheckout(\Enlight_Event_EventArgs $args)
    {
        /** @var \Shopware_Controllers_Frontend_Checkout $subject */
        $subject = $args->getSubject();
        $request = $subject->Request();
        $view = $subject->View();

        if ($request->has('bID')) {
            $basketID = $request->getParam('bID');

            $router = $this->container->get('router');
            $sBasketUrl = $router->assemble([
                'controller' => 'sharebasket',
                'action' => 'load',
                'bID' => $basketID,
            ]);

            $view->assign('froshShareBasket', true);
            $view->assign('sBasketUrl', $sBasketUrl);
        }
    }
}
