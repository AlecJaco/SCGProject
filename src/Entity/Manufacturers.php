<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Manufacturers
 *
 * @ORM\Table(name="manufacturers", indexes={@ORM\Index(name="name", columns={"manufacturer_id"})})
 * @ORM\Entity
 */
class Manufacturers
{
    /**
     * @var int
     *
     * @ORM\Column(name="manufacturer_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $manufacturerId;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=128, nullable=false)
     */
    private $name;

    public function getManufacturerId(): ?int
    {
        return $this->manufacturerId;
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


}
