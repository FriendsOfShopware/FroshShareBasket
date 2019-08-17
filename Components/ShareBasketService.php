<?php

namespace FroshShareBasket\Components;

use DateTime;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Enlight_Components_Db_Adapter_Pdo_Mysql;
use Enlight_Components_Session_Namespace;
use Enlight_Controller_Request_RequestHttp;
use Exception;
use FroshShareBasket\Models\ShareBasket;
use FroshShareBasket\Models\ShareBasketArticle;
use sBasket;
use Shopware\Bundle\StoreFrontBundle\Service\ContextServiceInterface;
use Shopware\Components\ContainerAwareEventManager;
use Shopware\Components\Model\ModelManager;
use Shopware\Components\Routing\Router;
use Shopware_Components_Modules;

class ShareBasketService implements ShareBasketServiceInterface
{
    /**
     * @var array
     */
    private $pluginConfig;

    /**
     * @var Shopware_Components_Modules;
     */
    private $modules;

    /**
     * @var Enlight_Components_Session_Namespace
     */
    private $session;

    /**
     * @var ModelManager
     */
    private $modelManager;

    /**
     * @var Connection;
     */
    private $connection;

    /**
     * @var Enlight_Components_Db_Adapter_Pdo_Mysql
     */
    private $db;

    /**
     * @var Router
     */
    private $router;

    /**
     * @var ContextServiceInterface
     */
    private $context;

    /**
     * @var ContainerAwareEventManager
     */
    private $events;

    /**
     * ShareBasketService constructor.
     *
     * @param array                                   $pluginConfig
     * @param Shopware_Components_Modules             $modules
     * @param Enlight_Components_Session_Namespace    $session
     * @param ModelManager                            $modelManager
     * @param Connection                              $connection
     * @param Enlight_Components_Db_Adapter_Pdo_Mysql $db
     * @param Router                                  $router
     * @param ContextServiceInterface                 $context
     * @param ContainerAwareEventManager              $events
     */
    public function __construct(
        array $pluginConfig,
        Shopware_Components_Modules $modules,
        Enlight_Components_Session_Namespace $session,
        ModelManager $modelManager,
        Connection $connection,
        Enlight_Components_Db_Adapter_Pdo_Mysql $db,
        Router $router,
        ContextServiceInterface $context,
        ContainerAwareEventManager $events
    ) {
        $this->pluginConfig = $pluginConfig;
        $this->modules = $modules;
        $this->session = $session;
        $this->modelManager = $modelManager;
        $this->connection = $connection;
        $this->db = $db;
        $this->router = $router;
        $this->context = $context;
        $this->events = $events;
    }

    /**
     * @throws \Enlight_Exception
     *
     * @return array
     */
    public function prepareBasketData(): array
    {
        $attributesToStore = $this->pluginConfig['attributesToStore'];

        /** @var sBasket $basketModule */
        $basketModule = $this->modules->getModule('sBasket');

        $BasketData = $basketModule->sGetBasketData();

        $data = [];
        foreach ($BasketData['content'] as $key => $article) {
            if ($article['modus'] === '2') {
                $voucher = $basketModule->sGetVoucher();
                $article['ordernumber'] = $voucher['code'];
            }

            $basketArticle = [
                'ordernumber' => $article['ordernumber'],
                'quantity' => $article['quantity'],
                'mode' => $article['modus'],
            ];

            foreach ($this->getBasketAttributes($article['id']) as $attribute => $value) {
                if ($value && in_array($attribute, $attributesToStore, false)) {
                    $basketArticle['attributes'][$attribute] = $value;
                }
            }

            if ($basketArticle['attributes'] !== null) {
                $basketArticle['attributes'] = serialize($basketArticle['attributes']);
            }

            $data['articles'][] = $basketArticle;
        }

        $data['hash'] = sha1(serialize($data['articles']));

        return $data;
    }

    /**
     * @param string                                 $shareBasketId
     * @param Enlight_Controller_Request_RequestHttp $request
     *
     * @throws Exception
     *
     * @return bool
     */
    public function loadBasket(string $shareBasketId, Enlight_Controller_Request_RequestHttp $request): bool
    {
        /** @var ShareBasket|null $shareBasket */
        $shareBasket = $this->modelManager->getRepository(ShareBasket::class)->findOneBy(['basketId' => $shareBasketId]);

        if ($shareBasket === null) {
            return false;
        }

        /** @var sBasket $basketModule */
        $basketModule = $this->modules->Basket();
        $basketModule->sDeleteBasket();

        /** @var ShareBasketArticle $shareBasketArticle */
        foreach ($shareBasket->getArticles() as $shareBasketArticle) {
            if ($shareBasketArticle->getMode() === 1) {
                $basketModule->sSYSTEM->_GET['sAddPremium'] = $shareBasketArticle->getOrdernumber();
                $basketModule->sInsertPremium();
            } elseif ($shareBasketArticle->getMode() === 2) {
                $basketModule->sAddVoucher($shareBasketArticle->getOrdernumber());
            } else {
                $attributes = unserialize($shareBasketArticle->getAttributes(), ['allowed_classes' => false]);

                if (!empty($attributes['swag_custom_products_configuration_hash'])) {
                    $request->setParam('customProductsHash', $attributes['swag_custom_products_configuration_hash']);
                }

                $this->events->notify('FroshShareBasket_Controller_loadAction_addArticle_Start', ['article' => $shareBasketArticle]);
                $insertId = $basketModule->sAddArticle($shareBasketArticle->getOrdernumber(), $shareBasketArticle->getQuantity() ?: 1);
                $insertId = $this->events->filter('FroshShareBasket_Controller_loadAction_addArticle_Added', $insertId);
                $this->updateBasketMode($shareBasketArticle->getMode(), $insertId);

                foreach ($attributes as $attribute => $value) {
                    if ($value !== null) {
                        $this->updateBasketPosition($insertId, $attribute, $value);
                    }
                }
            }

            $request->clearParams();
        }

        $this->session->offsetSet('froshShareBasketHash', $shareBasket->getHash());

        return true;
    }

    /**
     * @throws Exception
     *
     * @return bool|string
     */
    public function saveBasket()
    {
        $data = $this->prepareBasketData();

        $hash = $data['hash'];

        $shareBasket = $this->modelManager->getRepository(ShareBasket::class)->findOneBy(['hash' => $hash]);

        if ($shareBasket !== null) {
            $shareBasket->setCreated(new DateTime());

            if ($this->session->offsetGet('froshShareBasketHash') !== $hash) {
                $shareBasket->increaseSaveCount();
            }

            $this->modelManager->flush();

            return $this->generateBasketUrl($shareBasket->getBasketId(), false);
        }

        $shareBasket = new ShareBasket();
        foreach ($data['articles'] as $article) {
            $shareBasketArticleModel = new ShareBasketArticle();
            $shareBasketArticleModel->fromArray($article);
            $shareBasketArticleModel->setShareBasket($shareBasket);
            $shareBasket->addArticle($shareBasketArticleModel);
        }

        $shareBasket->setCreated(new DateTime());
        $shareBasket->setHash($hash);
        $shareBasket->setShopId($this->context->getShopContext()->getShop()->getId());

        try {
            return $this->persistShareBasket($shareBasket, $this->generateBasketId());
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * @param ShareBasket $shareBasket
     * @param string      $basketId
     * @param int         $attempts
     *
     * @throws Exception
     *
     * @return bool|string
     */
    private function persistShareBasket(ShareBasket $shareBasket, $basketId, $attempts = 0)
    {
        if ($attempts > 3) {
            return false;
        }

        if ($this->modelManager->getRepository(ShareBasket::class)->find($basketId) !== null) {
            $basketId = $this->generateBasketId();

            return $this->persistShareBasket($shareBasket, $basketId, $attempts++);
        }

        $shareBasket->setBasketId($basketId);
        $this->modelManager->persist($shareBasket);
        $this->modelManager->flush();
        $this->session->offsetSet('froshShareBasketHash', $shareBasket->getHash());

        return $this->generateBasketUrl($basketId);
    }

    /**
     * @throws Exception
     *
     * @return string
     */
    private function generateBasketId()
    {
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz-_';
        $basketId = '';
        for ($i = 0; $i < 11; ++$i) {
            $basketId .= $characters[random_int(0, 63)];
        }

        return $basketId;
    }

    /**
     * @param string $basketId
     * @param bool   $insert
     *
     * @return string
     */
    private function generateBasketUrl($basketId, $insert = true)
    {
        $path = 'loadBasket/' . $basketId;

        if ($insert) {
            /** @var \sRewriteTable $rewriteTableModule */
            $rewriteTableModule = $this->modules->getModule('sRewriteTable');
            $rewriteTableModule->sInsertUrl('sViewport=FroshShareBasket&sAction=load&bID=' . $basketId, $path);
        }

        return $this->router->assemble(['module' => 'frontend']) . $path;
    }

    /**
     * @param string $basketId
     *
     * @return mixed
     */
    private function getBasketAttributes($basketId)
    {
        /** @var QueryBuilder $builder */
        $builder = $this->connection->createQueryBuilder();
        $builder->select('attributes.*')
            ->from('s_order_basket', 'basket')
            ->innerJoin(
                'basket',
                's_order_basket_attributes',
                'attributes',
                'attributes.basketID = basket.id'
            )
            ->where('basket.id = :basketId')
            ->andWhere('sessionID = :sessionId')
            ->setParameters([
                ':basketId' => $basketId,
                ':sessionId' => $this->session->get('sessionId'),
            ]);
        $statement = $builder->execute();

        return $statement->fetch();
    }

    /**
     * @param $basketId
     * @param $field
     * @param $value
     */
    private function updateBasketPosition($basketId, $field, $value): void
    {
        $sql = 'UPDATE s_order_basket sob
		INNER JOIN s_order_basket_attributes soba ON ( soba.basketID = sob.id )
		SET soba.' . $field . ' = ?
		WHERE sob.id = ? AND sessionID = ?';

        try {
            $this->db->query(
                $sql,
                [
                    $value,
                    $basketId,
                    $this->session->get('sessionId'),
                ]
            );
        } catch (Exception $e) {
        }
    }

    /**
     * @param int    $mode
     * @param string $basketId
     */
    private function updateBasketMode($mode, $basketId): void
    {
        $builder = $this->connection->createQueryBuilder();
        $builder->update('s_order_basket')
            ->set('modus', (string) $mode)
            ->where('id = :basketId')
            ->andWhere('sessionId = :sessionId')
            ->setParameters([
                ':basketId' => $basketId,
                ':sessionId' => $this->session->get('sessionId'),
            ])
            ->execute();
    }
}
