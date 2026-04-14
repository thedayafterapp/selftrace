<?php

namespace App\Service;

use App\Entity\DailyPrompt;
use App\Entity\User;
use App\Repository\DailyPromptRepository;

class PromptRotationService
{
    public function __construct(
        private readonly DailyPromptRepository $promptRepository,
    ) {}

    public function getTodaysPrompt(User $user): ?DailyPrompt
    {
        $prompts = $this->promptRepository->findAllOrderedBySortOrder();
        if (empty($prompts)) {
            return null;
        }

        $dayOfYear = (int) (new \DateTime())->format('z'); // 0-364
        $userId = $user->getId() ?? 0;
        $index = ($dayOfYear + $userId) % count($prompts);

        return $prompts[$index];
    }
}
