<?php

namespace FroshShareBasket\Models;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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
class ShareBasket extends ModelEntity
{
    /**
     * @var Collection
     * @ORM\OneToMany(targetEntity="FroshShareBasket\Models\ShareBasketArticle", mappedBy="shareBasket", cascade={"persist"})
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
     * @return Collection
     */
    public function getArticles(): Collection
    {
        return $this->articles;
    }

    /**
     * @param Collection $articles
     */
    public function setArticles(Collection $articles): void
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
    public function getBasketId(): string
    {
        return $this->basketId;
    }

    /**
     * @param string $basketId
     */
    public function setBasketId(string $basketId): void
    {
        $this->basketId = $basketId;
    }

    /**
     * @return DateTime
     */
    public function getCreated(): DateTime
    {
        return $this->created;
    }

    /**
     * @param DateTime $created
     */
    public function setCreated(DateTime $created): void
    {
        $this->created = $created;
    }

    /**
     * @return string
     */
    public function getHash(): string
    {
        return $this->hash;
    }

    /**
     * @param string $hash
     */
    public function setHash(string $hash): void
    {
        $this->hash = $hash;
    }

    /**
     * @return int
     */
    public function getSaveCount(): int
    {
        return $this->saveCount;
    }

    /**
     * @param int $saveCount
     */
    public function setSaveCount(int $saveCount): void
    {
        $this->saveCount = $saveCount;
    }

    /**
     * @return int
     */
    public function getShopId(): int
    {
        return $this->shopId;
    }

    /**
     * @param int $shopId
     */
    public function setShopId(int $shopId): void
    {
        $this->shopId = $shopId;
    }

    /**
     * @param ShareBasketArticle $article
     *
     * @return $this
     */
    public function addArticle($article)
    {
        $this->articles->add($article);

        return $this;
    }

    /**
     * @param ShareBasketArticle $article
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
