<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Orders
 *
 * @ORM\Table(name="orders", indexes={@ORM\Index(name="user", columns={"user_id"})})
 * @ORM\Entity
 */
class Orders
{
    /**
     * @var int
     *
     * @ORM\Column(name="order_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $orderId;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="placed_time", type="datetime", nullable=false, options={"default"="CURRENT_TIMESTAMP"})
     */
    private $placedTime;

    /**
     * @var \Users
     *
     * @ORM\ManyToOne(targetEntity="Users")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="user_id", referencedColumnName="user_id")
     * })
     */
    private $user;

    public function __construct()
    {
        $this->placedTime = new \Datetime();
    }

    public function getOrderId(): ?int
    {
        return $this->orderId;
    }

    public function getPlacedTime(): ?\DateTimeInterface
    {
        return $this->placedTime;
    }

    public function setPlacedTime(\DateTimeInterface $placedTime): self
    {
        $this->placedTime = $placedTime;

        return $this;
    }

    public function getUser(): ?Users
    {
        return $this->user;
    }

    public function setUser(?Users $user): self
    {
        $this->user = $user;

        return $this;
    }


}
