<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Cards
 *
 * @ORM\Table(name="cards", indexes={@ORM\Index(name="order", columns={"order_id"}), @ORM\Index(name="manufacturer", columns={"manufacturer_id"})})
 * @ORM\Entity
 */
class Cards
{
    /**
     * @var int
     *
     * @ORM\Column(name="card_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $cardId;

    /**
     * @var string
     *
     * @ORM\Column(name="year", type="string", length=10, nullable=false)
     */
    private $year;

    /**
     * @var string
     *
     * @ORM\Column(name="set_name", type="string", length=128, nullable=false)
     */
    private $setName;

    /**
     * @var string
     *
     * @ORM\Column(name="card_number", type="string", length=64, nullable=false)
     */
    private $cardNumber;

    /**
     * @var string
     *
     * @ORM\Column(name="variation", type="string", length=512, nullable=false)
     */
    private $variation;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=512, nullable=false)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="sport", type="string", length=64, nullable=false)
     */
    private $sport;

    /**
     * @var float
     *
     * @ORM\Column(name="declared_value", type="float", precision=10, scale=0, nullable=false)
     */
    private $declaredValue;

    /**
     * @var \Orders
     *
     * @ORM\ManyToOne(targetEntity="Orders")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="order_id", referencedColumnName="order_id")
     * })
     */
    private $order;

    /**
     * @var \Manufacturers
     *
     * @ORM\ManyToOne(targetEntity="Manufacturers")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="manufacturer_id", referencedColumnName="manufacturer_id")
     * })
     */
    private $manufacturer;

    public function getCardId(): ?int
    {
        return $this->cardId;
    }

    public function getYear(): ?string
    {
        return $this->year;
    }

    public function setYear(string $year): self
    {
        $this->year = $year;

        return $this;
    }

    public function getSetName(): ?string
    {
        return $this->setName;
    }

    public function setSetName(string $setName): self
    {
        $this->setName = $setName;

        return $this;
    }

    public function getCardNumber(): ?string
    {
        return $this->cardNumber;
    }

    public function setCardNumber(string $cardNumber): self
    {
        $this->cardNumber = $cardNumber;

        return $this;
    }

    public function getVariation(): ?string
    {
        return $this->variation;
    }

    public function setVariation(string $variation): self
    {
        $this->variation = $variation;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getSport(): ?string
    {
        return $this->sport;
    }

    public function setSport(string $sport): self
    {
        $this->sport = $sport;

        return $this;
    }

    public function getDeclaredValue(): ?float
    {
        return $this->declaredValue;
    }

    public function setDeclaredValue(float $declaredValue): self
    {
        $this->declaredValue = $declaredValue;

        return $this;
    }

    public function getOrder(): ?Orders
    {
        return $this->order;
    }

    public function setOrder(?Orders $order): self
    {
        $this->order = $order;

        return $this;
    }

    public function getManufacturer(): ?Manufacturers
    {
        return $this->manufacturer;
    }

    public function setManufacturer(?Manufacturers $manufacturer): self
    {
        $this->manufacturer = $manufacturer;

        return $this;
    }


}
