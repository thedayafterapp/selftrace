<?php

namespace App\Controller;

use App\Entity\AiInsight;
use App\Entity\User;
use App\Repository\AiInsightRepository;
use App\Repository\ChapterRepository;
use App\Repository\PartRepository;
use App\Repository\ReflectionRepository;
use App\Service\ClaudeService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
#[Route('/ai')]
class AiController extends AbstractController
{
    #[Route('/throughline', name: 'ai_throughline', methods: ['POST'])]
    public function throughline(
        Request $request,
        ChapterRepository $chapterRepo,
        ClaudeService $claude,
    ): StreamedResponse {
        /** @var User $user */
        $user = $this->getUser();
        $chapters = array_reverse($chapterRepo->findByUserOrderedByDate($user));

        if (count($chapters) < 1) {
            return new StreamedResponse(function () {
                echo "Please add at least one chapter to your timeline first.";
                flush();
            });
        }

        $chaptersText = $this->formatChaptersForClaude($chapters);

        $prompt = "The following are chapters from someone's life — periods where they felt like a different version of themselves. Your job is to read across all of them and find the continuous thread. Look for: consistent values that appear in different forms, recurring fears or needs that show up regardless of which 'version' they were being, patterns in what they run toward or away from, and the qualities that were always present even when everything else changed. Write this as a warm, specific, personal narrative — not a list, not a report. Use their own words and specific details wherever possible. End with a paragraph that begins: 'This is your throughline.' Speak directly to them. Be specific. Be warm. Do not use clinical language. Do not give generic affirmations. This should feel like receiving a letter from someone who has known them their whole life and finally reflected them back.\n\n{$chaptersText}";

        return $this->streamClaudeResponse($claude, $prompt);
    }

    #[Route('/parts-synthesis', name: 'ai_parts_synthesis', methods: ['POST'])]
    public function partsSynthesis(
        PartRepository $partRepo,
        ClaudeService $claude,
    ): StreamedResponse {
        /** @var User $user */
        $user = $this->getUser();
        $parts = $partRepo->findByUser($user);

        if (count($parts) < 1) {
            return new StreamedResponse(function () {
                echo "Please add at least one part first.";
                flush();
            });
        }

        $partsText = $this->formatPartsForClaude($parts);

        $prompt = "The following are different 'parts' or recurring aspects of someone's personality — different modes they find themselves in. Your job is to read across all of them and find what they have in common underneath. What core need or fear is each part ultimately responding to? What is the person really trying to protect? Write this warmly and specifically, using their own words. Help them see that these parts aren't random or broken — they're all attempts to meet the same deep needs. Do not use clinical language. Do not use the term IFS. Speak directly to them.\n\n{$partsText}";

        return $this->streamClaudeResponse($claude, $prompt);
    }

    #[Route('/reflection-synthesis', name: 'ai_reflection_synthesis', methods: ['POST'])]
    public function reflectionSynthesis(
        ReflectionRepository $reflectionRepo,
        ClaudeService $claude,
    ): StreamedResponse {
        /** @var User $user */
        $user = $this->getUser();
        $reflections = $reflectionRepo->findByUserOrderedByDate($user);

        if (count($reflections) < 1) {
            return new StreamedResponse(function () {
                echo "Please write at least one reflection first.";
                flush();
            });
        }

        $reflectionsText = $this->formatReflectionsForClaude($reflections);

        $prompt = "The following are personal reflections written by someone over time. Read across all of them and tell them what you notice — the consistent themes, the things they keep returning to, the values and fears and needs that appear repeatedly even in different forms. Write this as a warm, personal narrative. Use their own words. Be specific, not generic. Help them see the continuous thread in their own thinking. Do not use clinical language. Speak directly to them.\n\n{$reflectionsText}";

        return $this->streamClaudeResponse($claude, $prompt);
    }

    #[Route('/save-insight', name: 'ai_save_insight', methods: ['POST'])]
    public function saveInsight(
        Request $request,
        EntityManagerInterface $em,
    ): JsonResponse {
        /** @var User $user */
        $user = $this->getUser();

        $data = json_decode($request->getContent(), true);
        $type = $data['type'] ?? '';
        $content = $data['content'] ?? '';

        $validTypes = [AiInsight::TYPE_THROUGHLINE, AiInsight::TYPE_REFLECTION_SYNTHESIS, AiInsight::TYPE_PARTS_SYNTHESIS];
        if (!in_array($type, $validTypes, true) || empty($content)) {
            return new JsonResponse(['error' => 'Invalid data'], 400);
        }

        $insight = new AiInsight();
        $insight->setUser($user);
        $insight->setType($type);
        $insight->setContent($content);
        $em->persist($insight);
        $em->flush();

        return new JsonResponse(['ok' => true]);
    }

    #[Route('/insight/{id}/delete', name: 'ai_insight_delete', methods: ['POST'])]
    public function deleteInsight(
        AiInsight $insight,
        Request $request,
        EntityManagerInterface $em,
    ): RedirectResponse {
        /** @var User $user */
        $user = $this->getUser();
        if ($insight->getUser() !== $user) {
            throw $this->createAccessDeniedException();
        }

        if ($this->isCsrfTokenValid('delete-insight-' . $insight->getId(), $request->request->get('_token'))) {
            $em->remove($insight);
            $em->flush();
        }

        return $this->redirectToRoute('patterns_index');
    }

    private function streamClaudeResponse(ClaudeService $claude, string $prompt): StreamedResponse
    {
        $response = new StreamedResponse(function () use ($claude, $prompt) {
            foreach ($claude->stream($prompt) as $chunk) {
                echo $chunk;
                if (ob_get_level() > 0) {
                    ob_flush();
                }
                flush();
            }
        });

        $response->headers->set('Content-Type', 'text/plain; charset=UTF-8');
        $response->headers->set('X-Accel-Buffering', 'no');
        $response->headers->set('Cache-Control', 'no-cache');

        return $response;
    }

    private function formatChaptersForClaude(array $chapters): string
    {
        $parts = [];
        $promptLabels = [
            'mattered' => 'What mattered to you then',
            'afraid' => 'What you were afraid of',
            'needed' => 'What you needed',
            'self_view' => 'How you saw yourself',
            'running' => 'What you were running toward or away from',
        ];

        foreach ($chapters as $chapter) {
            $text = "CHAPTER: {$chapter->getTitle()}\n";
            $start = $chapter->getStartDate()?->format('Y');
            $end = $chapter->isOngoing() ? 'present' : ($chapter->getEndDate()?->format('Y') ?? 'unknown');
            $text .= "Period: {$start}–{$end}\n";

            if ($chapter->getPartName()) {
                $text .= "Character name: {$chapter->getPartName()}\n";
            }

            $responses = $chapter->getPromptResponses() ?? [];
            foreach ($promptLabels as $key => $label) {
                if (!empty($responses[$key])) {
                    $text .= "{$label}: {$responses[$key]}\n";
                }
            }

            $parts[] = $text;
        }

        return implode("\n---\n", $parts);
    }

    private function formatPartsForClaude(array $parts): string
    {
        $texts = [];
        foreach ($parts as $part) {
            $text = "PART: {$part->getName()}\n";
            if ($part->getTriggerText()) {
                $text .= "What brings this part out: {$part->getTriggerText()}\n";
            }
            if ($part->getNeedsText()) {
                $text .= "What this part needs: {$part->getNeedsText()}\n";
            }
            if ($part->getFearsText()) {
                $text .= "What this part fears: {$part->getFearsText()}\n";
            }
            if ($part->getProtectsText()) {
                $text .= "What this part protects against: {$part->getProtectsText()}\n";
            }
            $texts[] = $text;
        }
        return implode("\n---\n", $texts);
    }

    private function formatReflectionsForClaude(array $reflections): string
    {
        $texts = [];
        foreach ($reflections as $reflection) {
            $date = $reflection->getDate()?->format('Y-m-d');
            $text = "DATE: {$date}\n";
            $text .= "PROMPT: {$reflection->getPromptText()}\n";
            $text .= "RESPONSE: {$reflection->getResponseText()}\n";
            $texts[] = $text;
        }
        return implode("\n---\n", $texts);
    }
}
