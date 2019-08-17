<?php

namespace FroshShareBasket\Models;

use Doctrine\ORM\Mapping as ORM;
use Shopware\Components\Model\ModelEntity;

/**
 * @ORM\Entity
 * @ORM\Table(name="s_plugin_sharebasket_articles",
 *     indexes={
 *          @ORM\Index(name="ordernumber", columns={"ordernumber"}),
 *      }
 * )
 */
class ShareBasketArticle extends ModelEntity
{
    /**
     * @var ShareBasket
     * @ORM\ManyToOne(targetEntity="FroshShareBasket\Models\ShareBasket", inversedBy="articles")
     * @ORM\JoinColumn(name="share_basket_id", referencedColumnName="id")
     */
    protected $shareBasket;

    /**
     * Unique identifier
     *
     * @var int
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(name="ordernumber", type="string")
     */
    private $ordernumber;

    /**
     * @var int
     * @ORM\Column(name="quantity", type="integer")
     */
    private $quantity = 1;

    /**
     * @var int
     * @ORM\Column(name="mode", type="integer")
     */
    private $mode = 0;

    /**
     * @var string|null
     * @ORM\Column(name="attributes", type="text", nullable=true)
     */
    private $attributes;

    /**
     * @return ShareBasket
     */
    public function getShareBasket(): ShareBasket
    {
        return $this->shareBasket;
    }

    /**
     * @param ShareBasket $shareBasket
     */
    public function setShareBasket(ShareBasket $shareBasket): void
    {
        $this->shareBasket = $shareBasket;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getOrdernumber(): string
    {
        return $this->ordernumber;
    }

    /**
     * @param string $ordernumber
     */
    public function setOrdernumber(string $ordernumber): void
    {
        $this->ordernumber = $ordernumber;
    }

    /**
     * @return int
     */
    public function getQuantity(): int
    {
        return $this->quantity;
    }

    /**
     * @param int $quantity
     */
    public function setQuantity(int $quantity): void
    {
        $this->quantity = $quantity;
    }

    /**
     * @return int
     */
    public function getMode(): int
    {
        return $this->mode;
    }

    /**
     * @param int $mode
     */
    public function setMode(int $mode): void
    {
        $this->mode = $mode;
    }

    /**
     * @return string|null
     */
    public function getAttributes(): ?string
    {
        return $this->attributes;
    }

    /**
     * @param string|null $attributes
     */
    public function setAttributes(?string $attributes): void
    {
        $this->attributes = $attributes;
    }
}
