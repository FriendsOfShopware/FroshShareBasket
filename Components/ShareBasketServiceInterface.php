<?php

namespace FroshShareBasket\Components;

interface ShareBasketServiceInterface
{
    /**
     * @throws \Enlight_Exception
     *
     * @return array
     */
    public function prepareBasketData();

    /**
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Enlight_Exception
     *
     * @return string
     */
    public function saveBasket();
}
