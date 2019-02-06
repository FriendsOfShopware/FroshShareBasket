<?php

namespace FroshShareBasket;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\SchemaTool;
use FroshShareBasket\Models\Article;
use FroshShareBasket\Models\Basket;
use Shopware\Components\Plugin;
use Shopware\Components\Plugin\Context\ActivateContext;
use Shopware\Components\Plugin\Context\DeactivateContext;
use Shopware\Components\Plugin\Context\InstallContext;
use Shopware\Components\Plugin\Context\UninstallContext;
use Shopware\Components\Plugin\Context\UpdateContext;

class FroshShareBasket extends Plugin
{
    /**
     * @param InstallContext $context
     */
    public function install(InstallContext $context)
    {
        $this->installSchema();
    }

    /**
     * @param UpdateContext $context
     */
    public function update(UpdateContext $context)
    {
        parent::update($context);
        $this->installSchema();
    }

    /**
     * @param UninstallContext $context
     *
     * @throws \Exception
     */
    public function uninstall(UninstallContext $context)
    {
        parent::uninstall($context);
        if (!$context->keepUserData()) {
            $this->uninstallSchema();
            $sql = file_get_contents($this->getPath() . '/Resources/sql/uninstall.sql');
            $this->container->get('shopware.db')->query($sql);
        }
    }

    /**
     * @param ActivateContext $context
     */
    public function activate(ActivateContext $context)
    {
        $context->scheduleClearCache(ActivateContext::CACHE_LIST_ALL);
    }

    /**
     * @param DeactivateContext $context
     */
    public function deactivate(DeactivateContext $context)
    {
        $context->scheduleClearCache(DeactivateContext::CACHE_LIST_ALL);
    }

    /**
     * Install or update s_plugin_sharebasket_baskets, s_plugin_sharebasket_articles table
     */
    private function installSchema()
    {
        /** @var EntityManager $em */
        $em =$this->container->get('models');

        $tool = new SchemaTool($em);

        $tool->updateSchema([
            $em->getClassMetadata(Article::class),
            $em->getClassMetadata(Basket::class),
        ], true);
    }

    /**
     * Remove s_plugin_sharebasket_baskets, s_plugin_sharebasket_articles table
     */
    private function uninstallSchema()
    {
        /** @var EntityManager $em */
        $em =$this->container->get('models');

        $tool = new SchemaTool($em);

        $tool->dropSchema([
            $em->getClassMetadata(Article::class),
            $em->getClassMetadata(Basket::class),
        ]);
    }
}
