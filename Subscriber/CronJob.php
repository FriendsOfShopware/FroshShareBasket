<?php

namespace FroshShareBasket\Subscriber;

use Doctrine\DBAL\Connection;
use Enlight\Event\SubscriberInterface;

class CronJob implements SubscriberInterface
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var array
     */
    private $pluginConfig;

    /**
     * CronJob constructor.
     *
     * @param Connection $connection
     * @param array      $pluginConfig
     */
    public function __construct(Connection $connection, array $pluginConfig)
    {
        $this->connection = $connection;
        $this->pluginConfig = $pluginConfig;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            'Shopware_CronJob_FroshShareBasketCleanup' => 'cleanup',
        ];
    }

    /**
     * @param \Shopware_Components_Cron_CronJob $job
     *
     * @throws \Doctrine\DBAL\DBALException
     *
     * @return string
     */
    public function cleanup(\Shopware_Components_Cron_CronJob $job)
    {
        $statement = $this->connection
            ->prepare('SET FOREIGN_KEY_CHECKS = 0;
              DELETE 
                  baskets, urls, articles
              FROM
                  s_plugin_sharebasket_baskets AS baskets 
              LEFT JOIN 
                  s_plugin_sharebasket_articles AS articles ON (baskets.id = articles.share_basket_id)
              LEFT JOIN 
                  s_core_rewrite_urls AS urls ON (urls.org_path = concat(:path,baskets.basket_id))
              WHERE
                  created < DATE_SUB(NOW(), INTERVAL :interval MONTH);
              SET FOREIGN_KEY_CHECKS = 1;
        ');

        $path = 'sViewport=FroshShareBasket&sAction=load&bID=';
        $statement->bindParam(':path', $path);
        $statement->bindParam(':interval', $this->pluginConfig['interval']);
        $statement->execute();

        return 'Deleted: ' . ($statement->rowCount() ?: '0');
    }
}
