<?php

namespace FroshShareBasket\Subscriber;

use Enlight\Event\SubscriberInterface;
use Shopware\Components\Routing\Router;

class Checkout implements SubscriberInterface
{
    /**
     * @var Router
     */
    private $router;

    /**
     * Checkout constructor.
     *
     * @param Router $router
     */
    public function __construct(Router $router)
    {
        $this->router = $router;
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

            $sBasketUrl = $this->router->assemble([
                'controller' => 'sharebasket',
                'action' => 'load',
                'bID' => $basketID,
            ]);

            $view->assign('froshShareBasket', true);
            $view->assign('sBasketUrl', $sBasketUrl);
        }
    }
}
