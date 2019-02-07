<?php

namespace FroshShareBasket\Models;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Shopware\Components\Model\ModelEntity;

/**
 * @ORM\Entity
 * @ORM\Table(name="s_plugin_sharebasket_baskets",
 *     uniqueConstraints={
 *        @ORM\UniqueConstraint(columns={"basket_id"}),
 *        @ORM\UniqueConstraint(columns={"hash"})
 *    }
 * )
 */
class Basket extends ModelEntity
{
    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="FroshShareBasket\Models\Article", mappedBy="basket", cascade={"persist"})
     */
    protected $articles;

    /**
     * Unique identifier
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(name="basket_id", type="string", nullable=false)
     */
    private $basketId;

    /**
     * @var DateTime
     * @ORM\Column(name="created", type="datetime", nullable=false)
     */
    private $created;

    /**
     * @var string
     * @ORM\Column(name="hash", type="string", nullable=false)
     */
    private $hash;

    /**
     * @var int
     * @ORM\Column(name="save_count", type="integer", nullable=false)
     */
    private $saveCount = 1;

    /**
     * @var int
     * @ORM\Column(name="shop_id", type="integer", nullable=false)
     */
    private $shopId;

    /**
     * Basket constructor.
     */
    public function __construct()
    {
        $this->articles = new ArrayCollection();
    }

    /**
     * @return ArrayCollection
     */
    public function getArticles()
    {
        return $this->articles;
    }

    /**
     * @param ArrayCollection $articles
     */
    public function setArticles($articles)
    {
        $this->articles = $articles;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getBasketID()
    {
        return $this->basketId;
    }

    /**
     * @param string $basketId
     */
    public function setBasketID($basketId)
    {
        $this->basketId = $basketId;
    }

    /**
     * @return DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * @param DateTime $created
     */
    public function setCreated($created)
    {
        $this->created = $created;
    }

    /**
     * @return string
     */
    public function getHash()
    {
        return $this->hash;
    }

    /**
     * @param string $hash
     */
    public function setHash($hash)
    {
        $this->hash = $hash;
    }

    /**
     * @return int
     */
    public function getSaveCount()
    {
        return $this->saveCount;
    }

    /**
     * @param int $saveCount
     */
    public function setSaveCount($saveCount)
    {
        $this->saveCount = $saveCount;
    }

    /**
     * @param Article $article
     *
     * @return $this
     */
    public function addArticle($article)
    {
        $this->articles->add($article);

        return $this;
    }

    /**
     * @return int
     */
    public function getShopId()
    {
        return $this->shopId;
    }

    /**
     * @param int $shopId
     */
    public function setShopId($shopId)
    {
        $this->shopId = $shopId;
    }

    /**
     * @param Article $article
     *
     * @return $this
     */
    public function removeArticle($article)
    {
        $this->articles->removeElement($article);

        return $this;
    }

    public function increaseSaveCount()
    {
        ++$this->saveCount;
    }
}
