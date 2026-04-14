<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\AiInsightRepository;
use App\Repository\ChapterRepository;
use App\Repository\PartRepository;
use App\Repository\ReflectionRepository;
use App\Service\PatternService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class PatternsController extends AbstractController
{
    #[Route('/patterns', name: 'patterns_index')]
    public function index(
        PatternService $patternService,
        ReflectionRepository $reflectionRepo,
        ChapterRepository $chapterRepo,
        PartRepository $partRepo,
        AiInsightRepository $insightRepo,
    ): Response {
        /** @var User $user */
        $user = $this->getUser();

        $topWords = $patternService->getTopWords($user);
        $insights = $insightRepo->findByUser($user);
        $writingDates = $reflectionRepo->getWritingDatesForHeatmap($user, 12);
        // Story sentences need chronological (oldest first) order
        $chapters = array_reverse($chapterRepo->findByUserOrderedByDate($user));

        // Merge chapter and part creation dates into the heatmap
        foreach ($chapters as $chapter) {
            $writingDates[$chapter->getCreatedAt()->format('Y-m-d')] = true;
        }
        foreach ($partRepo->findByUser($user) as $part) {
            $writingDates[$part->getCreatedAt()->format('Y-m-d')] = true;
        }

        // Build "your story so far" — one sentence per chapter
        $storySentences = [];
        foreach ($chapters as $chapter) {
            $start = $chapter->getStartDate()?->format('M j, Y');
            $end = $chapter->isOngoing() ? 'present' : ($chapter->getEndDate()?->format('M j, Y') ?? '?');
            $storySentences[] = sprintf(
                '%s (%s–%s)',
                $chapter->getTitle(),
                $start,
                $end
            );
        }

        // Build heatmap grid (last 12 weeks, aligned to Sunday)
        $heatmapDays = $this->buildHeatmapGrid($writingDates, 12);

        return $this->render('patterns/index.html.twig', [
            'top_words' => $topWords,
            'insights' => $insights,
            'heatmap_days' => $heatmapDays,
            'story_sentences' => $storySentences,
            'has_content' => !empty($topWords) || !empty($insights),
        ]);
    }

    /**
     * Build a flat list of days for the heatmap grid (84 days = 12 weeks)
     * @return array<array{date: string, has_entry: bool, is_future: bool}>
     */
    private function buildHeatmapGrid(array $writingDates, int $weeks): array
    {
        $days = [];
        $today = new \DateTime();

        // Start from the Sunday that is $weeks weeks ago
        $start = clone $today;
        $start->modify('-' . ($weeks * 7 - 1) . ' days');

        for ($i = 0; $i < $weeks * 7; $i++) {
            $day = clone $start;
            $day->modify("+{$i} days");
            $dateStr = $day->format('Y-m-d');
            $days[] = [
                'date' => $dateStr,
                'has_entry' => isset($writingDates[$dateStr]),
                'is_future' => $day > $today,
            ];
        }

        return $days;
    }
}
