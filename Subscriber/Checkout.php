<?php

namespace FroshShareBasket\Subscriber;

use Enlight\Event\SubscriberInterface;
use FroshShareBasket\Components\ShareBasketServiceInterface;

class Checkout implements SubscriberInterface
{
    /**
     * @var ShareBasketServiceInterface
     */
    private $shareBasketService;

    /**
     * @var \Enlight_Components_Session_Namespace
     */
    private $session;

    /**
     * Checkout constructor.
     *
     * @param ShareBasketServiceInterface           $shareBasketService
     * @param \Enlight_Components_Session_Namespace $session
     */
    public function __construct(ShareBasketServiceInterface $shareBasketService, \Enlight_Components_Session_Namespace $session)
    {
        $this->shareBasketService = $shareBasketService;
        $this->session = $session;
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

    public function onPostDispatchCheckout(\Enlight_Controller_ActionEventArgs $args)
    {
        /** @var \Shopware_Controllers_Frontend_Checkout $subject */
        $subject = $args->getSubject();
        $request = $subject->Request();
        $view = $subject->View();

        if ($request->has('shareBasketState')) {
            $view->assign('shareBasketState', $request->getParam('shareBasketState'));

            return;
        }

        if ($this->session->offsetExists('froshShareBasketHash')) {
            $hash = $this->session->offsetGet('froshShareBasketHash');
            $basketData = $this->shareBasketService->prepareBasketData();

            if ($hash === $basketData['hash']) {
                $view->assign('shareBasketState', 'basketexists');
                $view->assign('shareBasketUrl', $this->shareBasketService->saveBasket());
            }
        }
    }
}
