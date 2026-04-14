<?php

namespace App\Entity;

use App\Repository\PartRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PartRepository::class)]
class Part
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'parts')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $triggerText = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $needsText = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $fearsText = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $protectsText = null;

    #[ORM\Column(length: 7)]
    private string $colorHex = '#e8a5a0';

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }

    public function getUser(): ?User { return $this->user; }

    public function setUser(?User $user): static
    {
        $this->user = $user;
        return $this;
    }

    public function getName(): ?string { return $this->name; }

    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    public function getTriggerText(): ?string { return $this->triggerText; }

    public function setTriggerText(?string $triggerText): static
    {
        $this->triggerText = $triggerText;
        return $this;
    }

    public function getNeedsText(): ?string { return $this->needsText; }

    public function setNeedsText(?string $needsText): static
    {
        $this->needsText = $needsText;
        return $this;
    }

    public function getFearsText(): ?string { return $this->fearsText; }

    public function setFearsText(?string $fearsText): static
    {
        $this->fearsText = $fearsText;
        return $this;
    }

    public function getProtectsText(): ?string { return $this->protectsText; }

    public function setProtectsText(?string $protectsText): static
    {
        $this->protectsText = $protectsText;
        return $this;
    }

    public function getColorHex(): string { return $this->colorHex; }

    public function setColorHex(string $colorHex): static
    {
        $this->colorHex = $colorHex;
        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
}
