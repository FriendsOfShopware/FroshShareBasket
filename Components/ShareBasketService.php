<?php

namespace FroshShareBasket\Components;

use Doctrine\DBAL\Connection;
use FroshShareBasket\Models\Article;
use FroshShareBasket\Models\Basket;
use Shopware\Components\Model\ModelManager;
use Shopware\Components\Routing\Router;

class ShareBasketService
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
     * ShareBasketService constructor.
     *
     * @param array                                 $pluginConfig
     * @param \Shopware_Components_Modules          $modules
     * @param \Enlight_Components_Session_Namespace $session
     * @param ModelManager                          $modelManager
     * @param Connection                            $connection
     * @param Router                                $router
     */
    public function __construct(
        array $pluginConfig,
        \Shopware_Components_Modules $modules,
        \Enlight_Components_Session_Namespace $session,
        ModelManager $modelManager,
        Connection $connection,
        Router $router
    ) {
        $this->pluginConfig = $pluginConfig;
        $this->modules = $modules;
        $this->session = $session;
        $this->modelManager = $modelManager;
        $this->connection = $connection;
        $this->router = $router;
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
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Enlight_Exception
     *
     * @return string
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

            return $this->generateBasketUrl($basket->getBasketID());
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

        $maxAttempts = 3;
        $attempts = 0;

        do {
            try {
                $basketId = $this->generateBasketId();
                $basketModel->setBasketID($basketId);
                $this->modelManager->persist($basketModel);
                $this->modelManager->flush();

                $this->session->offsetSet('froshShareBasketHash', $hash);

                return $this->generateBasketUrl($basketId);
            } catch (Exception $e) {
                ++$attempts;
                continue;
            }
            break;
        } while (
            $attempts <= $maxAttempts
        );
    }

    /**
     * @throws \Exception
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
     * @param bool $insert
     *
     * @return string
     */
    public function generateBasketUrl($basketId, $insert = true)
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
     * @param $basketId
     *
     * @return mixed
     */
    public function getBasketAttributes($basketId)
    {
        /** @var \Doctrine\DBAL\Query\QueryBuilder $builder */
        $builder = $this->connection->createQueryBuilder();
        $builder->select('soba.*')
            ->from('s_order_basket', 'sob')
            ->innerJoin('sob', 's_order_basket_attributes', 'soba', 'soba.basketID = sob.id')
            ->where('sob.id = :basketID')
            ->andWhere('sessionID = :sessionID')
            ->setParameters([
                ':basketID' => $basketId,
                ':sessionID' => $this->session->get('sessionId'),
            ]);
        $statement = $builder->execute();

        return $statement->fetch();
    }
}
