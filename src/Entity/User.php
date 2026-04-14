<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[ORM\HasLifecycleCallbacks]
#[UniqueEntity(fields: ['username'], message: 'That username is already taken')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    private ?string $username = null;

    #[ORM\Column]
    private array $roles = [];

    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column]
    private bool $onboardingComplete = false;

    #[ORM\OneToMany(targetEntity: Chapter::class, mappedBy: 'user', orphanRemoval: true)]
    private Collection $chapters;

    #[ORM\OneToMany(targetEntity: Part::class, mappedBy: 'user', orphanRemoval: true)]
    private Collection $parts;

    #[ORM\OneToMany(targetEntity: Reflection::class, mappedBy: 'user', orphanRemoval: true)]
    private Collection $reflections;

    #[ORM\OneToMany(targetEntity: AiInsight::class, mappedBy: 'user', orphanRemoval: true)]
    private Collection $aiInsights;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->chapters = new ArrayCollection();
        $this->parts = new ArrayCollection();
        $this->reflections = new ArrayCollection();
        $this->aiInsights = new ArrayCollection();
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

    public function getUserIdentifier(): string
    {
        return (string) $this->username;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';
        return array_unique($roles);
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;
        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;
        return $this;
    }

    public function eraseCredentials(): void {}

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function isOnboardingComplete(): bool
    {
        return $this->onboardingComplete;
    }

    public function setOnboardingComplete(bool $onboardingComplete): static
    {
        $this->onboardingComplete = $onboardingComplete;
        return $this;
    }

    /** @return Collection<int, Chapter> */
    public function getChapters(): Collection
    {
        return $this->chapters;
    }

    /** @return Collection<int, Part> */
    public function getParts(): Collection
    {
        return $this->parts;
    }

    /** @return Collection<int, Reflection> */
    public function getReflections(): Collection
    {
        return $this->reflections;
    }

    /** @return Collection<int, AiInsight> */
    public function getAiInsights(): Collection
    {
        return $this->aiInsights;
    }
}
