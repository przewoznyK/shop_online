<?php

namespace App\Entity;

use App\Repository\OrderProductRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OrderProductRepository::class)]
class OrderProduct
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'orderProducts')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $owner = null;

    #[ORM\ManyToOne(inversedBy: 'orderProducts')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $buyer = null;

    #[ORM\Column(length: 255)]
    private ?string $product = null;

    #[ORM\Column(length: 255)]
    private ?string $price = null;

    #[ORM\Column]
    private ?bool $ispaid = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $comment = null;

    #[ORM\Column(length: 255)]
    private ?string $delivery_type = null;

    #[ORM\Column(length: 255)]
    private ?string $final_location = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    private ?string $lastName = null;

    #[ORM\Column(length: 255)]
    private ?string $email = null;

    #[ORM\Column]
    private ?int $phoneNumber = null;

    #[ORM\Column(length: 255)]
    private ?string $paymentMethod = null;

    #[ORM\Column(length: 255)]
    private ?string $address = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): self
    {
        $this->owner = $owner;

        return $this;
    }

    public function getBuyer(): ?User
    {
        return $this->buyer;
    }

    public function setBuyer(?User $buyer): self
    {
        $this->buyer = $buyer;

        return $this;
    }

    public function getProduct(): ?string
    {
        return $this->product;
    }

    public function setProduct(string $product): self
    {
        $this->product = $product;

        return $this;
    }

    public function getPrice(): ?string
    {
        return $this->price;
    }

    public function setPrice(string $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function isIspaid(): ?bool
    {
        return $this->ispaid;
    }

    public function setIspaid(bool $ispaid): self
    {
        $this->ispaid = $ispaid;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): self
    {
        $this->comment = $comment;

        return $this;
    }

    public function getDeliveryType(): ?string
    {
        return $this->delivery_type;
    }

    public function setDeliveryType(string $delivery_type): self
    {
        $this->delivery_type = $delivery_type;

        return $this;
    }

    public function getFinalLocation(): ?string
    {
        return $this->final_location;
    }

    public function setFinalLocation(string $final_location): self
    {
        $this->final_location = $final_location;

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

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getPhoneNumber(): ?int
    {
        return $this->phoneNumber;
    }

    public function setPhoneNumber(int $phoneNumber): self
    {
        $this->phoneNumber = $phoneNumber;

        return $this;
    }

    public function getPaymentMethod(): ?string
    {
        return $this->paymentMethod;
    }

    public function setPaymentMethod(string $paymentMethod): self
    {
        $this->paymentMethod = $paymentMethod;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(string $address): self
    {
        $this->address = $address;

        return $this;
    }
}
