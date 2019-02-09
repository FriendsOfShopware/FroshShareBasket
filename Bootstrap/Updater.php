<?php

namespace FroshShareBasket\Bootstrap;

use Doctrine\DBAL\Connection;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Shop\DetachedShop;

class Updater
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var \sRewriteTable
     */
    private $rewriteTableModule;

    /**
     * @var ModelManager
     */
    private $modelManager;

    /**
     * @var string
     */
    private $pluginDir;

    /**
     * Updater constructor.
     *
     * @param Connection     $connection
     * @param \sRewriteTable $rewriteTableModule
     * @param ModelManager   $modelManager
     * @param string         $pluginDir
     */
    public function __construct(
        Connection $connection,
        \sRewriteTable $rewriteTableModule,
        ModelManager $modelManager,
        string $pluginDir
    ) {
        $this->connection = $connection;
        $this->rewriteTableModule = $rewriteTableModule;
        $this->modelManager = $modelManager;
        $this->pluginDir = $pluginDir;
    }

    /**
     * @param string $oldVersion
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    public function update($oldVersion)
    {
        if (version_compare($oldVersion, '1.1.0', '<')) {
            $this->prepareBaskets();
        }
    }

    /**
     * @throws \Doctrine\DBAL\DBALException
     */
    private function prepareBaskets()
    {
        $sql = file_get_contents($this->pluginDir . '/Resources/sql/update.1.1.0.sql');
        $this->connection->query($sql);

        $sql = 'SELECT * FROM `s_plugin_sharebasket_baskets`';

        $baskets = $this->connection->executeQuery($sql)->fetchAll();

        foreach ($baskets as $basket) {
            $hash = sha1(serialize($basket));
            $sql = 'UPDATE `s_plugin_sharebasket_baskets` SET hash = :hash WHERE id = :id';
            $this->connection->executeUpdate($sql, ['hash' => $hash, ':id' => $basket['id']]);

            $articles = json_decode($basket['articles'], true);
            foreach ($articles as $article) {
                $data = [
                    ':share_basket_id' => $basket['id'],
                    ':ordernumber' => $article['ordernumber'],
                    ':quantity' => $article['quantity'],
                    ':mode' => $article['modus'],
                    ':attributes' => $article['attributes'] ? serialize($article['attributes']) : null,
                ];

                $sql = 'INSERT INTO `s_plugin_sharebasket_articles` (`share_basket_id`, `ordernumber`, `quantity`, `mode`, `attributes`) VALUES (:share_basket_id, :ordernumber, :quantity, :mode, :attributes)';
                $this->connection->executeUpdate($sql, $data);
            }

            $this->generateSeoUrl($basket['basketID']);
        }
    }

    /**
     * @param string $basketId
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    private function generateSeoUrl($basketId)
    {
        $shops = $this->connection->executeQuery('SELECT id FROM `s_core_shops` WHERE active = 1')->fetchAll(\PDO::FETCH_COLUMN);
        $path = 'sharebasket/load/bID/' . $basketId;

        /** @var \Shopware\Models\Shop\Repository $repository */
        $repository = $this->modelManager->getRepository(\Shopware\Models\Shop\Shop::class);

        foreach ($shops as $shopId) {
            /** @var DetachedShop|null $shop */
            $shop = $repository->getActiveById($shopId);
            if ($shop === null) {
                throw new \Doctrine\DBAL\DBALException('No valid shop id passed');
            }
            $shop->registerResources();

            $this->rewriteTableModule->sInsertUrl('sViewport=FroshShareBasket&sAction=load&bID=' . $basketId, $path);
        }
    }
}
