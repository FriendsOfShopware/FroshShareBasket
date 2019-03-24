<?php

use FroshShareBasket\Models\Article;
use FroshShareBasket\Models\Basket;
use Shopware\Components\CSRFGetProtectionAware;

class Shopware_Controllers_Frontend_FroshShareBasket extends Enlight_Controller_Action implements CSRFGetProtectionAware
{
    /**
     * @return array
     */
    public function getCSRFProtectedActions()
    {
        return [
            'save',
        ];
    }

    /**
     * @throws \Exception
     */
    public function loadAction()
    {
        $basketRepository = $this->container->get('models')->getRepository(Basket::class);

        /** @var Basket|null $basket */
        $basket = $basketRepository->findOneBy(['basketId' => $this->Request()->getParam('bID')]);

        if ($basket === null) {
            $this->forward('cart', 'checkout', 'frontend', ['shareBasketState' => 'basketnotfound']);

            return;
        }

        /** @var sBasket $basketModule */
        $basketModule = $this->container->get('modules')->Basket();
        $basketModule->sDeleteBasket();

        /** @var Article $article */
        foreach ($basket->getArticles() as $article) {
            if ($article->getMode() === 1) {
                $this->container->get('system')->_GET['sAddPremium'] = $article->getOrdernumber();
                $basketModule->sInsertPremium();
            } elseif ($article->getMode() === 2) {
                $basketModule->sAddVoucher($article->getOrdernumber());
            } else {
                $attributes = PHP_MAJOR_VERSION >= 7 ? unserialize($article->getAttributes(), ['allowed_classes'=>false]) : unserialize($article->getAttributes());

                if (!empty($attributes['swag_custom_products_configuration_hash'])) {
                    $this->container->get('front')->Request()->setParam('customProductsHash', $attributes['swag_custom_products_configuration_hash']);
                }

                $this->container->get('events')->notify('FroshShareBasket_Controller_loadAction_addArticle_Start', ['article' => $article]);
                $insertId = $basketModule->sAddArticle($article->getOrdernumber(), $article->getQuantity() ?: 1);
                $insertId = $this->container->get('events')->filter('FroshShareBasket_Controller_loadAction_addArticle_Added', $insertId);
                $this->updateBasketMode($article->getMode(), $insertId);

                foreach ($attributes as $attribute => $value) {
                    if ($value !== null) {
                        $this->updateBasketPosition($insertId, $attribute, $value);
                    }
                }
            }

            $this->container->get('front')->Request()->clearParams();
        }

        $this->container->get('session')->offsetSet('froshShareBasketHash', $basket->getHash());

        $this->forward('cart', 'checkout', 'frontend', ['shareBasketState' => 'basketloaded']);
    }

    /**
     * @throws Exception
     */
    public function saveAction()
    {
        $shareBasketService = $this->container->get('frosh_share_basket.components.share_basket_service');
        $shareBasketUrl = $shareBasketService->saveBasket();

        if ($shareBasketUrl === false) {
            $this->View()->assign('shareBasketState', 'basketsavefailed');
        } else {
            $this->View()->assign('shareBasketUrl', $shareBasketUrl);
            $this->View()->assign('shareBasketState', 'basketsaved');
        }
    }

    /**
     * @param string $basketId
     * @param string $field
     * @param string $value
     */
    public function updateBasketPosition($basketId, $field, $value)
    {
        $sql = 'UPDATE
			s_order_basket sob
		INNER JOIN s_order_basket_attributes soba ON (
			soba.basketID = sob.id
		)
		SET
			soba.' . $field . ' = ?
		WHERE
			sob.id = ?
			AND sessionID = ?';

        try {
            $this->container->get('db')->query(
                $sql,
                [
                    $value,
                    $basketId,
                    $this->container->get('session')->get('sessionId'),
                ]
            );
        } catch (Exception $e) {
        }
    }

    /**
     * @param int    $mode
     * @param string $basketId
     */
    public function updateBasketMode($mode, $basketId)
    {
        /** @var \Doctrine\DBAL\Query\QueryBuilder $builder */
        $builder = $this->container->get('dbal_connection')->createQueryBuilder();
        $builder->update('s_order_basket')
            ->set('modus', (string) $mode)
            ->where('id = :basketId')
            ->andWhere('sessionId = :sessionId')
            ->setParameters([
                ':basketId' => $basketId,
                ':sessionId' => $this->container->get('session')->get('sessionId'),
            ])
            ->execute();
    }
}
