<?php

namespace App\Entity;

use App\Repository\RechargeRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RechargeRepository::class)]
class Recharge
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(nullable: true)]
    private ?int $quantite = null;

    #[ORM\Column(nullable: true)]
    private ?int $oldQuantite = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $date = null;

    #[ORM\ManyToOne]
    private ?User $utilisateur = null;

    #[ORM\ManyToOne]
    private ?User $clientid = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getQuantite(): ?int
    {
        return $this->quantite;
    }

    public function setQuantite(?int $quantite): static
    {
        $this->quantite = $quantite;

        return $this;
    }

    public function getOldQuantite(): ?int
    {
        return $this->oldQuantite;
    }

    public function setOldQuantite(?int $oldQuantite): static
    {
        $this->oldQuantite = $oldQuantite;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(?\DateTimeInterface $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getUtilisateur(): ?User
    {
        return $this->utilisateur;
    }

    public function setUtilisateur(?User $utilisateur): static
    {
        $this->utilisateur = $utilisateur;

        return $this;
    }

    public function getClientid(): ?User
    {
        return $this->clientid;
    }

    public function setClientid(?User $clientid): static
    {
        $this->clientid = $clientid;

        return $this;
    }
}
