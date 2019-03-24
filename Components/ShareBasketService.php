<?php

namespace FroshShareBasket\Components;

use Doctrine\DBAL\Connection;
use FroshShareBasket\Models\Article;
use FroshShareBasket\Models\Basket;
use Shopware\Bundle\StoreFrontBundle\Service\ContextServiceInterface;
use Shopware\Components\Model\ModelManager;
use Shopware\Components\Routing\Router;

class ShareBasketService implements ShareBasketServiceInterface
{
    /**
     * @var array
     */
    private $pluginConfig;

    /**
     * @var \Shopware_Components_Modules;
     */
    private $modules;

    /**
     * @var \Enlight_Components_Session_Namespace
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
     * @var Router
     */
    private $router;

    /**
     * @var ContextServiceInterface
     */
    private $context;

    /**
     * ShareBasketService constructor.
     *
     * @param array                                 $pluginConfig
     * @param \Shopware_Components_Modules          $modules
     * @param \Enlight_Components_Session_Namespace $session
     * @param ModelManager                          $modelManager
     * @param Connection                            $connection
     * @param Router                                $router
     * @param ContextServiceInterface               $context
     */
    public function __construct(
        array $pluginConfig,
        \Shopware_Components_Modules $modules,
        \Enlight_Components_Session_Namespace $session,
        ModelManager $modelManager,
        Connection $connection,
        Router $router,
        ContextServiceInterface $context
    ) {
        $this->pluginConfig = $pluginConfig;
        $this->modules = $modules;
        $this->session = $session;
        $this->modelManager = $modelManager;
        $this->connection = $connection;
        $this->router = $router;
        $this->context = $context;
    }

    /**
     * @throws \Enlight_Exception
     *
     * @return array
     */
    public function prepareBasketData()
    {
        $attributesToStore = $this->pluginConfig['attributesToStore'];

        /** @var \sBasket $basketModule */
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
     * @throws \Exception
     *
     * @return bool|string
     */
    public function saveBasket()
    {
        $data = $this->prepareBasketData();

        $hash = $data['hash'];

        $basket = $this->modelManager->getRepository(Basket::class)->findOneBy(['hash' => $hash]);

        if ($basket !== null) {
            $basket->setCreated(new \DateTime());

            if ($this->session->offsetGet('froshShareBasketHash') !== $hash) {
                $basket->increaseSaveCount();
            }

            $this->modelManager->flush();

            return $this->generateBasketUrl($basket->getBasketID(), false);
        }

        $basketModel = new Basket();
        foreach ($data['articles'] as $article) {
            $articleModel = new Article();
            $articleModel->fromArray($article);
            $articleModel->setBasket($basketModel);
            $basketModel->addArticle($articleModel);
        }

        $basketModel->setCreated(new \DateTime());
        $basketModel->setHash($hash);
        $basketModel->setShopId($this->context->getShopContext()->getShop()->getId());

        try {
            return $this->persistShareBasket($basketModel, $this->generateBasketId());
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * @param Basket $basketModel
     * @param string $basketId
     * @param int    $attempts
     *
     * @throws \Exception
     *
     * @return bool|string
     */
    private function persistShareBasket(Basket $basketModel, $basketId, $attempts = 0)
    {
        if ($attempts > 3) {
            return false;
        }

        if ($this->modelManager->getRepository(Basket::class)->find($basketId) !== null) {
            $basketId = $this->generateBasketId();

            return $this->persistShareBasket($basketModel, $basketId, $attempts++);
        }

        $basketModel->setBasketID($basketId);
        $this->modelManager->persist($basketModel);
        $this->modelManager->flush();
        $this->session->offsetSet('froshShareBasketHash', $basketModel->getHash());

        return $this->generateBasketUrl($basketId);
    }

    /**
     * @throws \Exception
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
        /** @var \Doctrine\DBAL\Query\QueryBuilder $builder */
        $builder = $this->connection->createQueryBuilder();
        $builder->select('soba.*')
            ->from('s_order_basket', 'sob')
            ->innerJoin('sob', 's_order_basket_attributes', 'soba', 'soba.basketID = sob.id')
            ->where('sob.id = :basketId')
            ->andWhere('sessionID = :sessionId')
            ->setParameters([
                ':basketId' => $basketId,
                ':sessionId' => $this->session->get('sessionId'),
            ]);
        $statement = $builder->execute();

        return $statement->fetch();
    }
}
