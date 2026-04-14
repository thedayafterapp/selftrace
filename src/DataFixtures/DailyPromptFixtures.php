<?php

namespace App\DataFixtures;

use App\Entity\DailyPrompt;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

class DailyPromptFixtures extends Fixture implements FixtureGroupInterface
{
    private const PROMPTS = [
        "Describe a moment this week where you felt most like yourself. What were you doing?",
        "What's something consistent about you that shows up no matter which version of you is present?",
        "Think of a strong reaction you had recently. What was happening underneath it — what did you need or fear?",
        "If the you from 5 years ago could see you now, what would surprise them? What wouldn't?",
        "What's a value you've held across every chapter of your life, even when everything else changed?",
        "When do you feel least like yourself? What's usually happening?",
        "What kind of people have you always been drawn to? What does that tell you about what you value?",
        "Describe something you've always come back to, no matter how many times you've walked away from it.",
        "What does 'home' feel like to you — not a place, but a feeling? When have you felt it?",
        "What are you most proud of that has nothing to do with achievement?",
        "What do you wish people understood about you that they usually don't?",
        "Think about the different versions of you across your life. What were they all reaching for?",
        "When have you surprised yourself? What did that reveal?",
        "What do you protect fiercely, even when you're not sure why?",
        "Describe a moment you felt genuinely seen. What was happening?",
        "What has stayed true about you since childhood?",
        "What do your closest relationships have in common?",
        "What do you keep returning to creatively, intellectually, or emotionally?",
        "When do you feel most at peace? What conditions make that possible?",
        "What are you afraid of wanting, in case you don't get it?",
        "What would the most continuous, stable version of you look like? What would they know?",
        "What have you outgrown? What haven't you?",
        "What do you stand for, even when it costs you something?",
        "Describe a time you acted from your deepest values. How did it feel?",
        "What story have you been telling yourself about who you are? Is it true?",
        "What do the hardest periods of your life have in common?",
        "What do your best periods of your life have in common?",
        "What part of yourself do you find hardest to accept? What might it be trying to do for you?",
        "If you could send one message to every version of yourself across time, what would it be?",
        "What does today's version of you know that yesterday's didn't?",
    ];

    public static function getGroups(): array
    {
        return ['prompts'];
    }

    public function load(ObjectManager $manager): void
    {
        foreach (self::PROMPTS as $index => $promptText) {
            $prompt = new DailyPrompt();
            $prompt->setPromptText($promptText);
            $prompt->setSortOrder($index);
            $manager->persist($prompt);
        }

        $manager->flush();
    }
}
