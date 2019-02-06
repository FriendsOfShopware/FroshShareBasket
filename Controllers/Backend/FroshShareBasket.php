<?php

use FroshShareBasket\Models\Basket;

class Shopware_Controllers_Backend_FroshShareBasket extends Shopware_Controllers_Backend_Application
{
    protected $model = Basket::class;
    protected $alias = 'basket';

    public function preDispatch()
    {
        parent::preDispatch();
        $this->View()->addTemplateDir($this->container->getParameter('frosh_share_basket.view_dir'));
    }
}
