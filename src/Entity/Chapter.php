<?php

namespace App\Entity;

use App\Repository\ChapterRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ChapterRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Chapter
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'chapters')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $startDate = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $endDate = null;

    #[ORM\Column]
    private bool $isOngoing = false;

    #[ORM\Column(length: 7)]
    private string $colorHex = '#f59e0b';

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $partName = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $promptResponses = null;

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

    public function getTitle(): ?string { return $this->title; }

    public function setTitle(string $title): static
    {
        $this->title = $title;
        return $this;
    }

    public function getStartDate(): ?\DateTimeInterface { return $this->startDate; }

    public function setStartDate(\DateTimeInterface $startDate): static
    {
        $this->startDate = $startDate;
        return $this;
    }

    public function getEndDate(): ?\DateTimeInterface { return $this->endDate; }

    public function setEndDate(?\DateTimeInterface $endDate): static
    {
        $this->endDate = $endDate;
        return $this;
    }

    public function isOngoing(): bool { return $this->isOngoing; }

    public function setIsOngoing(bool $isOngoing): static
    {
        $this->isOngoing = $isOngoing;
        return $this;
    }

    public function getColorHex(): string { return $this->colorHex; }

    public function setColorHex(string $colorHex): static
    {
        $this->colorHex = $colorHex;
        return $this;
    }

    public function getPartName(): ?string { return $this->partName; }

    public function setPartName(?string $partName): static
    {
        $this->partName = $partName;
        return $this;
    }

    public function getPromptResponses(): ?array { return $this->promptResponses; }

    public function setPromptResponses(?array $promptResponses): static
    {
        $this->promptResponses = $promptResponses;
        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }

    public function getPreview(): string
    {
        if (!$this->promptResponses) {
            return '';
        }
        $responses = array_filter(array_values($this->promptResponses));
        if (empty($responses)) {
            return '';
        }
        $text = implode(' ', $responses);
        return mb_strlen($text) > 150 ? mb_substr($text, 0, 150) . '…' : $text;
    }
}
