<?php

namespace FroshShareBasket\Subscriber;

use Enlight\Event\SubscriberInterface;
use Shopware\Components\DependencyInjection\Container as DIContainer;

class CronJob implements SubscriberInterface
{
    /**
     * @var DIContainer
     */
    private $container;

    /**
     * @var array
     */
    private $pluginConfig;

    /**
     * CronJob constructor.
     *
     * @param DIContainer $container
     * @param array       $pluginConfig
     */
    public function __construct(DIContainer $container, array $pluginConfig)
    {
        $this->container = $container;
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
        $builder = $this->container->get('dbal_connection')->createQueryBuilder();
        $result = $builder->delete('s_plugin_sharebasket_baskets')
            ->where('created < DATE_SUB(NOW(), INTERVAL :interval MONTH)')
            ->setParameter(':interval', $this->pluginConfig['interval'])
            ->execute();

        return 'Deleted: ' . ($result ?: '0');
    }
}
