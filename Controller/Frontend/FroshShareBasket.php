<?php

namespace FroshShareBasket\Controller\Frontend;

use Enlight_Controller_Action;
use Exception;
use FroshShareBasket\Components\ShareBasketService;
use Shopware\Components\CSRFGetProtectionAware;

class FroshShareBasket extends Enlight_Controller_Action implements CSRFGetProtectionAware
{
    /**
     * @var ShareBasketService
     */
    private $shareBasketService;

    /**
     * FroshShareBasket constructor.
     *
     * @param ShareBasketService $shareBasketService
     */
    public function __construct(ShareBasketService $shareBasketService)
    {
        $this->shareBasketService = $shareBasketService;
        parent::__construct();
    }

    /**
     * @return array
     */
    public function getCSRFProtectedActions(): array
    {
        return [
            'save',
        ];
    }

    /**
     * @param string $bID
     *
     * @throws Exception
     */
    public function loadAction(string $bID): void
    {
        if (!$this->shareBasketService->loadBasket($bID, $this->Request())) {
            $this->forward(
                'cart',
                'checkout',
                'frontend',
                [
                    'shareBasketState' => 'basketnotfound',
                ]
            );

            return;
        }

        $this->forward(
            'cart',
            'checkout',
            'frontend',
            [
                'shareBasketState' => 'basketloaded',
            ]
        );
    }

    /**
     * @throws Exception
     */
    public function saveAction(): void
    {
        $shareBasketUrl = $this->shareBasketService->saveBasket();

        if ($shareBasketUrl === false) {
            $this->View()->assign('shareBasketState', 'basketsavefailed');
        } else {
            $this->View()->assign('shareBasketUrl', $shareBasketUrl);
            $this->View()->assign('shareBasketState', 'basketsaved');
        }
    }
}
