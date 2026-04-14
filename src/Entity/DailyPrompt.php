<?php

namespace App\Entity;

use App\Repository\DailyPromptRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DailyPromptRepository::class)]
class DailyPrompt
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $promptText = null;

    #[ORM\Column]
    private ?int $sortOrder = null;

    public function getId(): ?int { return $this->id; }

    public function getPromptText(): ?string { return $this->promptText; }

    public function setPromptText(string $promptText): static
    {
        $this->promptText = $promptText;
        return $this;
    }

    public function getSortOrder(): ?int { return $this->sortOrder; }

    public function setSortOrder(int $sortOrder): static
    {
        $this->sortOrder = $sortOrder;
        return $this;
    }
}
