<?php

namespace App\Entity;

use App\Repository\DeliveryRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DeliveryRepository::class)]
class Delivery
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $type = null;

    #[ORM\ManyToOne(inversedBy: 'delivery')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Product $product = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $personal_pickup = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $delivery_time = null;

    #[ORM\Column]
    private ?int $price = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(?Product $product): self
    {
        $this->product = $product;

        return $this;
    }

    public function getPersonalPickup(): ?string
    {
        return $this->personal_pickup;
    }

    public function setPersonalPickup(string $personal_pickup): self
    {
        $this->personal_pickup = $personal_pickup;

        return $this;
    }

    public function getDeliveryTime(): ?\DateTimeInterface
    {
        return $this->delivery_time;
    }

    public function setDeliveryTime(\DateTimeInterface $delivery_time): self
    {
        $this->delivery_time = $delivery_time;

        return $this;
    }

    public function getPrice(): ?int
    {
        return $this->price;
    }

    public function setPrice(int $price): self
    {
        $this->price = $price;

        return $this;
    }
}
