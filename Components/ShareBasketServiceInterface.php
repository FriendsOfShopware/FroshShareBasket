<?php

namespace FroshShareBasket\Components;

use Enlight_Controller_Request_RequestHttp;

interface ShareBasketServiceInterface
{
    /**
     * @return array
     */
    public function prepareBasketData(): array;

    /**
     * @param string                                 $shareBasketId
     * @param Enlight_Controller_Request_RequestHttp $request
     *
     * @return bool
     */
    public function loadBasket(string $shareBasketId, Enlight_Controller_Request_RequestHttp $request): bool;

    /**
     * @return bool|string
     */
    public function saveBasket();
}
