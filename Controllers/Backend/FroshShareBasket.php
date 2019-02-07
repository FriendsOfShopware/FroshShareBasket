<?php

class Shopware_Controllers_Backend_FroshShareBasket extends Shopware_Controllers_Backend_ExtJs
{
    public function getStatisticsAction()
    {
        $connection = $this->container->get('dbal_connection');
        $query = $connection->createQueryBuilder();
        $query->select(
                [
                    'articles.ordernumber as ordernumber',
                    'SUM(1 * save_count) as total_count',
                    'SUM(articles.quantity * save_count) as total_quantity',
                    'CONCAT(sArticles.name," ", details.additionaltext) as name',
                    'sArticles.id as articleId',
                ]
            )
            ->from('s_plugin_sharebasket_baskets', 'baskets')
            ->innerJoin('baskets', 's_plugin_sharebasket_articles', 'articles', 'baskets.id = articles.share_basket_id')
            ->leftJoin('articles', 's_articles_details', 'details', 'articles.ordernumber = details.ordernumber')
            ->leftJoin('details', 's_articles', 'sArticles', 'details.articleID = sArticles.id')
            ->where('articles.mode = 0')
            ->groupBy('articles.ordernumber');

        $idList = (string) $this->Request()->getParam('selectedShops');
        if (!empty($idList)) {
            $selectedShopIds = explode(',', $idList);

            foreach ($selectedShopIds as $shopId) {
                $query->addSelect('SUM(IF(baskets.shop_id = ' . $connection->quote($shopId) . ', 1, 0) * save_count)  as total_count' . $shopId);
                $query->addSelect('SUM(IF(baskets.shop_id = ' . $connection->quote($shopId) . ', articles.quantity, 0) * save_count)  as total_quantity' . $shopId);
            }
        }

        if ($this->getFromDate() instanceof \DateTime) {
            $query->andWhere('created >= :fromDate')
                ->setParameter('fromDate', $this->getFromDate()->format('Y-m-d H:i:s'));
        }
        if ($this->getToDate() instanceof \DateTime) {
            $query->andWhere('created <= :toDate')
                ->setParameter('toDate', $this->getToDate()->format('Y-m-d H:i:s'));
        }

        $sort = $this->Request()->getParam('sort', [
            [
                'property' => 'total_count',
                'direction' => 'DESC',
            ],
        ]);
        if ($sort) {
            foreach ($sort as $condition) {
                $query->addOrderBy(
                    $condition['property'],
                    $condition['direction']
                );
            }
        }

        $data = $query->execute()->fetchAll();

        $totalCount = $query->getConnection()->fetchColumn(
            'SELECT FOUND_ROWS() as count'
        );

        $this->View()->assign([
            'success' => true,
            'data' => $data,
            'count' => $totalCount,
        ]);
    }

    /**
     * @throws Exception
     *
     * @return DateTime|mixed
     */
    private function getFromDate()
    {
        $fromDate = $this->Request()->getParam('fromDate');
        if (empty($fromDate)) {
            $fromDate = new \DateTime();
            $fromDate = $fromDate->sub(new DateInterval('P1M'));
        } else {
            $fromDate = new \DateTime($fromDate);
        }

        return $fromDate;
    }

    /**
     * @throws Exception
     *
     * @return DateTime|mixed
     */
    private function getToDate()
    {
        //if a to date passed, format it over the \DateTime object. Otherwise create a new date with today
        $toDate = $this->Request()->getParam('toDate');
        if (empty($toDate)) {
            $toDate = new \DateTime();
        } else {
            $toDate = new \DateTime($toDate);
        }
        //to get the right value cause 2012-02-02 is smaller than 2012-02-02 15:33:12
        $toDate = $toDate->add(new DateInterval('P1D'));
        $toDate = $toDate->sub(new DateInterval('PT1S'));

        return $toDate;
    }
}
