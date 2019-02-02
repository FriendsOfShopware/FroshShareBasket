<?php

namespace FroshShareBasket\Subscriber;

use Enlight\Event\SubscriberInterface;

class Checkout implements SubscriberInterface
{
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
        if ($request->has('shareBasketState')) {
            $view->assign('shareBasketState', $request->getParam('shareBasketState'));
        }
    }
}
