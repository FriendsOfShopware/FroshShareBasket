<?php

namespace FroshShareBasket\Models;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Shopware\Components\Model\ModelEntity;

/**
 * @ORM\Entity
 * @ORM\Table(name="s_plugin_sharebasket_baskets",
 *     uniqueConstraints={
 *        @ORM\UniqueConstraint(columns={"basketID"}),
 *        @ORM\UniqueConstraint(columns={"hash"})
 *    }
 * )
 */
class Basket extends ModelEntity
{
    /**
     * Unique identifier
     *
     * @var int
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var int
     * @ORM\Column(name="basketID", type="string", nullable=false)
     */
    protected $basketID;

    /**
     * @var string
     * @ORM\Column(name="articles", type="text", nullable=false)
     */
    protected $articles;

    /**
     * @var DateTime
     * @ORM\Column(name="created", type="datetime", nullable=false)
     */
    protected $created;

    /**
     * @var string
     * @ORM\Column(name="hash", type="string", nullable=false)
     */
    private $hash;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getBasketID()
    {
        return $this->basketID;
    }

    /**
     * @param int $basketID
     */
    public function setBasketID($basketID)
    {
        $this->basketID = $basketID;
    }

    /**
     * @return string
     */
    public function getArticles()
    {
        return $this->articles;
    }

    /**
     * @param string $articles
     */
    public function setArticles($articles)
    {
        $this->articles = $articles;
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
}
