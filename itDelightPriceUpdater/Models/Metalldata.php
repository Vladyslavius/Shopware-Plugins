<?php

namespace itDelightPriceUpdater\Models;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Shopware\Components\Model\ModelEntity;

/**
 * @ORM\Entity
 * @ORM\Table(name="s_price_updater_metall_data", options={"collate"="utf8_unicode_ci"})
 */
class Metalldata extends ModelEntity
{
    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column()
     */
    private $code;

    /**
     * @var decimal
     *
     * @ORM\Column(type="decimal", precision=10, scale=6)
     */
    private $price;

    /**
     * @var \DateTimeInterface
     *
     * @ORM\Column(name="changetime", type="datetime", nullable=false)
     */
    private $changed;

    public function __construct()
    {
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $code
     */
    public function setCode($code)
    {
        $this->code = $code;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param float $price
     */
    public function setPrice($price)
    {
        $this->price = $price;
    }

    /**
     * @return float
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * Set changed
     *
     * @param \DateTimeInterface|string $changed
     *
     * @return Article
     */
    public function setChanged($changed = "now")
    {
        if (!$changed instanceof \DateTimeInterface) {
            $this->changed = new \DateTime($changed);
        } else {
            $this->changed = $changed;
        }

        return $this;
    }

    /**
     * Get changed
     *
     * @return \DateTimeInterface
     */
    public function getChanged()
    {
        return $this->changed;
    }
}
