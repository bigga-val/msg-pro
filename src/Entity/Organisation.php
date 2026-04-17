<?php

namespace App\Entity;

use App\Repository\OrganisationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OrganisationRepository::class)]
class Organisation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $designation = null;

    #[ORM\ManyToOne]
    private ?TypeOrganisation $type = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $rccm = null;

    #[ORM\Column(length: 40, nullable: true)]
    private ?string $idnat = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $adresse = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $expediteur = null;

    #[ORM\ManyToOne(inversedBy: 'organisations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column(nullable: true)]
    private ?bool $isApproved = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $createdAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDesignation(): ?string
    {
        return $this->designation;
    }

    public function setDesignation(?string $designation): static
    {
        $this->designation = $designation;

        return $this;
    }

    public function getType(): ?TypeOrganisation
    {
        return $this->type;
    }

    public function setType(?TypeOrganisation $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getRccm(): ?string
    {
        return $this->rccm;
    }

    public function setRccm(?string $rccm): static
    {
        $this->rccm = $rccm;

        return $this;
    }

    public function getIdnat(): ?string
    {
        return $this->idnat;
    }

    public function setIdnat(?string $idnat): static
    {
        $this->idnat = $idnat;

        return $this;
    }

    public function getAdresse(): ?string
    {
        return $this->adresse;
    }

    public function setAdresse(?string $adresse): static
    {
        $this->adresse = $adresse;

        return $this;
    }

    public function getExpediteur(): ?string
    {
        return $this->expediteur;
    }

    public function setExpediteur(?string $expediteur): static
    {
        $this->expediteur = $expediteur;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function isApproved(): ?bool
    {
        return $this->isApproved;
    }

    public function setApproved(?bool $isApproved): static
    {
        $this->isApproved = $isApproved;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTimeInterface $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }
}
