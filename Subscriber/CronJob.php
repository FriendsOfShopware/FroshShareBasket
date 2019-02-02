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
     * @return string
     */
    public function cleanup(\Shopware_Components_Cron_CronJob $job)
    {
        /** @var \Doctrine\DBAL\Query\QueryBuilder $builder */
        $builder = $this->connection->createQueryBuilder();
        $result = $builder->delete('s_plugin_sharebasket_baskets')
            ->where('created < DATE_SUB(NOW(), INTERVAL :interval MONTH)')
            ->setParameter(':interval', $this->pluginConfig['interval'])
            ->execute();

        return 'Deleted: ' . ($result ?: '0');
    }
}
