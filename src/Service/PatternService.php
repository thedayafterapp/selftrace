<?php

namespace App\Service;

use App\Entity\User;
use App\Repository\ChapterRepository;
use App\Repository\PartRepository;
use App\Repository\ReflectionRepository;

class PatternService
{
    private const STOP_WORDS = [
        'i', 'me', 'my', 'myself', 'we', 'our', 'ours', 'ourselves', 'you', 'your', 'yours',
        'yourself', 'yourselves', 'he', 'him', 'his', 'himself', 'she', 'her', 'hers', 'herself',
        'it', 'its', 'itself', 'they', 'them', 'their', 'theirs', 'themselves', 'what', 'which',
        'who', 'whom', 'this', 'that', 'these', 'those', 'am', 'is', 'are', 'was', 'were', 'be',
        'been', 'being', 'have', 'has', 'had', 'having', 'do', 'does', 'did', 'doing', 'a', 'an',
        'the', 'and', 'but', 'if', 'or', 'because', 'as', 'until', 'while', 'of', 'at', 'by',
        'for', 'with', 'about', 'against', 'between', 'into', 'through', 'during', 'before',
        'after', 'above', 'below', 'to', 'from', 'up', 'down', 'in', 'out', 'on', 'off', 'over',
        'under', 'again', 'further', 'then', 'once', 'here', 'there', 'when', 'where', 'why',
        'how', 'all', 'both', 'each', 'few', 'more', 'most', 'other', 'some', 'such', 'no', 'nor',
        'not', 'only', 'own', 'same', 'so', 'than', 'too', 'very', 's', 't', 'can', 'will',
        'just', 'don', 'should', 'now', 'd', 'll', 'm', 'o', 're', 've', 'y', 'ain', 'aren',
        'couldn', 'didn', 'doesn', 'hadn', 'hasn', 'haven', 'isn', 'ma', 'mightn', 'mustn',
        'needn', 'shan', 'shouldn', 'wasn', 'weren', 'won', 'wouldn', 'like', 'feel', 'felt',
        'think', 'thought', 'know', 'knew', 'want', 'wanted', 'get', 'got', 'make', 'made',
        'see', 'saw', 'come', 'came', 'go', 'went', 'really', 'always', 'never', 'still', 'even',
        'also', 'would', 'could', 'might', 'much', 'many', 'one', 'two', 'three', 'first',
        'last', 'every', 'time', 'way', 'something', 'anything', 'nothing', 'everything',
        'someone', 'anyone', 'everyone', 'thing', 'things', 'lot', 'bit', 'kind', 'sort',
        'part', 'put', 'look', 'looked', 'looking', 'try', 'tried', 'trying', 'seem', 'seemed',
        'give', 'gave', 'take', 'took', 'say', 'said', 'tell', 'told', 'let', 'may', 'back',
        'around', 'long', 'little', 'big', 'great', 'good', 'bad', 'right', 'left', 'old',
        'new', 'different', 'sure', 'hard', 'high', 'low', 'next', 'able', 'need', 'needs',
        'felt', 'find', 'found', 'keep', 'kept', 'though', 'thought', 'without', 'within',
        'those', 'whether', 'well', 'often', 'sometimes', 'already', 'someone', 'something',
        'quite', 'since', 'doesn', 'weren', 'didn', 'couldn', 'aren', 'isn', 'wasn', 'haven',
    ];

    public function __construct(
        private readonly ChapterRepository $chapterRepository,
        private readonly PartRepository $partRepository,
        private readonly ReflectionRepository $reflectionRepository,
    ) {}

    /**
     * Returns top N words by frequency across all user content
     * @return array<string, int>
     */
    public function getTopWords(User $user, int $limit = 20): array
    {
        $text = $this->collectAllText($user);
        if (empty(trim($text))) {
            return [];
        }

        // Lowercase and split on non-alpha characters
        $text = mb_strtolower($text);
        $words = preg_split('/[^a-z\']+/', $text, -1, PREG_SPLIT_NO_EMPTY);
        if (!$words) {
            return [];
        }

        $stopWords = array_flip(self::STOP_WORDS);
        $counts = [];

        foreach ($words as $word) {
            // Strip leading/trailing apostrophes
            $word = trim($word, "'");
            if (mb_strlen($word) < 3) {
                continue;
            }
            if (isset($stopWords[$word])) {
                continue;
            }
            $counts[$word] = ($counts[$word] ?? 0) + 1;
        }

        arsort($counts);
        return array_slice($counts, 0, $limit, true);
    }

    private function collectAllText(User $user): string
    {
        $parts = [];

        foreach ($this->chapterRepository->findByUserOrderedByDate($user) as $chapter) {
            $parts[] = $chapter->getTitle() ?? '';
            $parts[] = $chapter->getPartName() ?? '';
            if ($chapter->getPromptResponses()) {
                foreach ($chapter->getPromptResponses() as $response) {
                    $parts[] = (string) $response;
                }
            }
        }

        foreach ($this->partRepository->findByUser($user) as $part) {
            $parts[] = $part->getName() ?? '';
            $parts[] = $part->getTriggerText() ?? '';
            $parts[] = $part->getNeedsText() ?? '';
            $parts[] = $part->getFearsText() ?? '';
            $parts[] = $part->getProtectsText() ?? '';
        }

        foreach ($this->reflectionRepository->findByUserOrderedByDate($user) as $reflection) {
            $parts[] = $reflection->getResponseText() ?? '';
        }

        return implode(' ', array_filter($parts));
    }
}
