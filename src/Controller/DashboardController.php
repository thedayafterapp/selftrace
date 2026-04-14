<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\ChapterRepository;
use App\Repository\PartRepository;
use App\Repository\ReflectionRepository;
use App\Repository\AiInsightRepository;
use App\Service\PromptRotationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'dashboard')]
    public function index(
        ChapterRepository $chapterRepo,
        PartRepository $partRepo,
        ReflectionRepository $reflectionRepo,
        AiInsightRepository $insightRepo,
        PromptRotationService $promptService,
    ): Response {
        /** @var User $user */
        $user = $this->getUser();

        if (!$user->isOnboardingComplete()) {
            return $this->redirectToRoute('onboarding_step1');
        }

        $chapters = $chapterRepo->findByUserOrderedByDate($user);
        $parts = $partRepo->findByUser($user);
        $todayPrompt = $promptService->getTodaysPrompt($user);
        $todayReflection = $reflectionRepo->findTodayByUser($user);
        $recentReflections = array_slice($reflectionRepo->findByUserOrderedByDate($user), 0, 3);
        $latestInsight = $insightRepo->findLatestByUserAndType($user, 'throughline');

        return $this->render('dashboard/index.html.twig', [
            'chapters' => $chapters,
            'parts' => $parts,
            'today_prompt' => $todayPrompt,
            'today_reflection' => $todayReflection,
            'recent_reflections' => $recentReflections,
            'latest_insight' => $latestInsight,
            'chapter_count' => count($chapters),
            'part_count' => count($parts),
            'reflection_count' => count($reflectionRepo->findByUserOrderedByDate($user)),
        ]);
    }
}
