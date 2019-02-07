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
class Article extends ModelEntity
{
    /**
     * @var Basket
     * @ORM\ManyToOne(targetEntity="FroshShareBasket\Models\Basket", inversedBy="articles")
     * @ORM\JoinColumn(name="share_basket_id", referencedColumnName="id")
     */
    protected $basket;

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
     * @var string
     * @ORM\Column(name="attributes", type="text", nullable=true)
     */
    private $attributes;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Basket
     */
    public function getBasket()
    {
        return $this->basket;
    }

    /**
     * @param Basket $basket
     */
    public function setBasket($basket)
    {
        $this->basket = $basket;
    }

    /**
     * @return string
     */
    public function getOrdernumber()
    {
        return $this->ordernumber;
    }

    /**
     * @param string $ordernumber
     */
    public function setOrdernumber($ordernumber)
    {
        $this->ordernumber = $ordernumber;
    }

    /**
     * @return int
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * @param int $quantity
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;
    }

    /**
     * @return int
     */
    public function getMode()
    {
        return $this->mode;
    }

    /**
     * @param int $mode
     */
    public function setMode($mode)
    {
        $this->mode = $mode;
    }

    /**
     * @return string
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * @param string $attributes
     */
    public function setAttributes($attributes)
    {
        $this->attributes = $attributes;
    }
}
