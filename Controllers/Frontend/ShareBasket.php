<?php

use Shopware\Components\CSRFGetProtectionAware;

class Shopware_Controllers_Frontend_ShareBasket extends Enlight_Controller_Action implements CSRFGetProtectionAware
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
        $request = $this->Request();
        $basket = $this->getBasket($request->getParam('bID'));

        if (empty($basket)) {
            $this->forward('cart', 'checkout', 'frontend', ['shareBasketState' => 'basketnotfound']);

            return;
        }

        /** @var sBasket $basketModule */
        $basketModule = $this->container->get('modules')->Basket();
        $basketModule->sDeleteBasket();

        $articles = unserialize($basket['articles']);

        foreach ($articles as $article) {
            if ((int) $article['modus'] === 1) {
                $this->container->get('system')->_GET['sAddPremium'] = $article['ordernumber'];
                $basketModule->sInsertPremium();
            } elseif ((int) $article['modus'] === 2) {
                $basketModule->sAddVoucher($article['ordernumber']);
            } else {
                $this->container->get('events')->notify('FroshShareBasket_Controller_loadAction_addArticle_Start', ['article' => $article]);
                $insertId = $basketModule->sAddArticle($article['ordernumber'], $article['quantity'] ?: 1);
                $insertId = $this->container->get('events')->filter('FroshShareBasket_Controller_loadAction_addArticle_Added', $insertId);
                $this->updateBasketMode($article['modus'], $insertId);
            }

            foreach ($article['attributes'] as $attribute => $value) {
                if ($value !== null) {
                    $this->updateBasketPosition($insertId, $attribute, $value);
                }
            }
        }

        $this->forward('cart', 'checkout', 'frontend', ['shareBasketState' => 'basketloaded']);
    }

    /**
     * @throws \Exception
     */
    public function saveAction()
    {
        $attributesToStore = $this->container->get('config')->getByNamespace('FroshShareBasket', 'attributesToStore');

        /** @var sBasket $basketModule */
        $basketModule = $this->container->get('modules')->Basket();

        $BasketData = $basketModule->sGetBasketData();

        $articles = [];
        foreach ($BasketData['content'] as $key => $article) {
            if ((int) $article['modus'] === 2) {
                $voucher = $basketModule->sGetVoucher();
                $article['ordernumber'] = $voucher['code'];
            }

            $basketArticle = [
                'ordernumber' => $article['ordernumber'],
                'quantity' => $article['quantity'],
                'modus' => $article['modus'],
            ];

            foreach ($this->getBasketAttributes($article['id']) as $attribute => $value) {
                if ($attribute !== 'id' && $attribute !== 'basketID' && $value !== null && in_array($attribute, $attributesToStore, false)) {
                    $basketArticle['attributes'][$attribute] = $value;
                }
            }

            $articles[] = $basketArticle;
        }

        $shareBasketPath = $this->saveBasket(serialize($articles));

        $router = $this->container->get('router');
        $shareBasketUrl = $router->assemble(['module' => 'frontend']) . $shareBasketPath;

        $this->View()->assign('shareBasketUrl', $shareBasketUrl);
    }

    /**
     * @param $basketId
     *
     * @return mixed
     */
    public function getBasket($basketId)
    {
        /** @var \Doctrine\DBAL\Query\QueryBuilder $builder */
        $builder = $this->container->get('dbal_connection')->createQueryBuilder();
        $builder->select('*')
            ->from('s_plugin_sharebasket_baskets')
            ->where('basketID = :basketID')
            ->setParameter(':basketID', $basketId);

        $statement = $builder->execute();

        return $statement->fetch();
    }

    /**
     * @param $articles
     *
     * @throws \Doctrine\DBAL\DBALException
     *
     * @return string
     */
    public function saveBasket($articles)
    {
        $hash = sha1($articles);
        $created = date('Y-m-d H:i:s');

        /** @var \Doctrine\DBAL\Query\QueryBuilder $builder */
        $builder = $this->container->get('dbal_connection')->createQueryBuilder();
        $builder->select('basketID')
            ->from('s_plugin_sharebasket_baskets')
            ->where('hash = :hash')
            ->setParameter(':hash', $hash);
        $basketId = $builder->execute()->fetch(\PDO::FETCH_COLUMN);

        if (!empty($basketId)) {
            /** @var \Doctrine\DBAL\Query\QueryBuilder $builder */
            $builder = $this->container->get('dbal_connection')->createQueryBuilder();
            $builder->update('s_plugin_sharebasket_baskets')
                ->set('created', ':created')
                ->where('basketID = :basketId')
                ->setParameters([
                    ':created' => $created,
                    ':basketId' => $basketId,
                ])
                ->execute();

            return $this->generateBasketUrl($basketId, false);
        }

        $statement = $this->container->get('dbal_connection')
            ->prepare('INSERT IGNORE INTO s_plugin_sharebasket_baskets (basketId, articles, created, hash) VALUES (:basketID, :articles, :created, :hash)');
        $statement->bindParam(':articles', $articles);
        $statement->bindParam(':created', $created);
        $statement->bindParam(':hash', $hash);
        do {
            $basketId = $this->generateBasketId();
            $statement->bindParam(':basketID', $basketId);
            $statement->execute();
            $shareBasketPath = $this->generateBasketUrl($basketId);
        } while (
            $statement->rowCount() === 0
        );

        return $shareBasketPath;
    }

    /**
     * @throws Exception
     *
     * @return string
     */
    public function generateBasketId()
    {
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz-_';
        $basketId = '';
        for ($i = 0; $i < 11; ++$i) {
            $basketId .= $characters[random_int(0, 63)];
        }

        return $basketId;
    }

    /**
     * @param $basketId
     * @param $insert
     *
     * @return string
     */
    public function generateBasketUrl($basketId, $insert = true)
    {
        $path = 'loadBasket/' . $basketId;

        if ($insert) {
            /** @var \sRewriteTable $rewriteTableModule */
            $rewriteTableModule = $this->container->get('modules')->sRewriteTable();
            $rewriteTableModule->sInsertUrl('sViewport=ShareBasket&sAction=load&bID=' . $basketId, $path);
        }

        return $path;
    }

    /**
     * @param $basketId
     *
     * @return mixed
     */
    public function getBasketAttributes($basketId)
    {
        /** @var \Doctrine\DBAL\Query\QueryBuilder $builder */
        $builder = $this->container->get('dbal_connection')->createQueryBuilder();
        $builder->select('soba.*')
            ->from('s_order_basket', 'sob')
            ->innerJoin('sob', 's_order_basket_attributes', 'soba', 'soba.basketID = sob.id')
            ->where('sob.id = :basketID')
            ->andWhere('sessionID = :sessionID')
            ->setParameters([
                ':basketID' => $basketId,
                ':sessionID' => $this->container->get('session')->get('sessionId'),
            ]);
        $statement = $builder->execute();

        return $statement->fetch();
    }

    /**
     * @param $basketId
     * @param $field
     * @param $value
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
     * @param $modus
     * @param $basketId
     */
    public function updateBasketMode($modus, $basketId)
    {
        /** @var \Doctrine\DBAL\Query\QueryBuilder $builder */
        $builder = $this->container->get('dbal_connection')->createQueryBuilder();
        $builder->update('s_order_basket')
            ->set('modus', $modus)
            ->where('id = :basketID')
            ->andWhere('sessionID = :sessionID')
            ->setParameters([
                ':basketID' => $basketId,
                ':sessionID' => $this->container->get('session')->get('sessionId'),
            ])
            ->execute();
    }
}
