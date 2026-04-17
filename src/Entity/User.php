<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_USERNAME', fields: ['username'])]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['Email'])]
#[ORM\Index(columns: ['Email'], name: 'idx_user_email')]
#[UniqueEntity(fields: ['username'], message: 'There is already an account with this username')]
#[UniqueEntity(fields: ['Email'], message: 'Cette adresse email est déjà utilisée.')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    private ?string $username = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(length: 180, nullable: true)]
    private ?string $Email = null;

    /**
     * @var Collection<int, Organisation>
     */
    #[ORM\OneToMany(targetEntity: Organisation::class, mappedBy: 'user')]
    private Collection $organisations;

    #[ORM\Column(length: 15, nullable: true)]
    private ?string $telephone = null;

    #[ORM\Column(nullable: true)]
    private ?int $totalSMS = null;

    #[ORM\Column(nullable: true)]
    private ?int $usedSMS = null;

    #[ORM\Column(options: ['default' => false])]
    private bool $confirmer = false;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $confirmationToken = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dateCreation = null;

    public function __construct()
    {
        $this->organisations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->username;
    }

    /**
     * @see UserInterface
     *
     * @return list<string>
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getEmail(): ?string
    {
        return $this->Email;
    }

    public function setEmail(?string $Email): static
    {
        $this->Email = $Email;

        return $this;
    }

    /**
     * @return Collection<int, Organisation>
     */
    public function getOrganisations(): Collection
    {
        return $this->organisations;
    }

    public function addOrganisation(Organisation $organisation): static
    {
        if (!$this->organisations->contains($organisation)) {
            $this->organisations->add($organisation);
            $organisation->setUser($this);
        }

        return $this;
    }

    public function removeOrganisation(Organisation $organisation): static
    {
        if ($this->organisations->removeElement($organisation)) {
            // set the owning side to null (unless already changed)
            if ($organisation->getUser() === $this) {
                $organisation->setUser(null);
            }
        }

        return $this;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(?string $telephone): static
    {
        $this->telephone = $telephone;

        return $this;
    }

    public function getTotalSMS(): ?int
    {
        return $this->totalSMS;
    }

    public function setTotalSMS(?int $totalSMS): static
    {
        $this->totalSMS = $totalSMS;

        return $this;
    }

    public function getUsedSMS(): ?int
    {
        return $this->usedSMS;
    }

    public function setUsedSMS(?int $usedSMS): static
    {
        $this->usedSMS = $usedSMS;

        return $this;
    }

    public function isConfirmer(): bool
    {
        return $this->confirmer;
    }

    public function setConfirmer(bool $confirmer): static
    {
        $this->confirmer = $confirmer;

        return $this;
    }

    public function getDateCreation(): ?\DateTimeInterface
    {
        return $this->dateCreation;
    }

    public function setDateCreation(?\DateTimeInterface $dateCreation): static
    {
        $this->dateCreation = $dateCreation;

        return $this;
    }

    public function getConfirmationToken(): ?string
    {
        return $this->confirmationToken;
    }

    public function setConfirmationToken(?string $confirmationToken): static
    {
        $this->confirmationToken = $confirmationToken;

        return $this;
    }
}
