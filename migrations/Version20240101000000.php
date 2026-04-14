<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240101000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Initial Selftrace schema with seed data';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE `user` (
            id INT AUTO_INCREMENT NOT NULL,
            username VARCHAR(180) NOT NULL,
            roles JSON NOT NULL COMMENT \'(DC2Type:json)\',
            password VARCHAR(255) NOT NULL,
            created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            onboarding_complete TINYINT(1) NOT NULL DEFAULT 0,
            UNIQUE INDEX UNIQ_8D93D649F85E0677 (username),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE chapter (
            id INT AUTO_INCREMENT NOT NULL,
            user_id INT NOT NULL,
            title VARCHAR(255) NOT NULL,
            start_date DATE NOT NULL,
            end_date DATE DEFAULT NULL,
            is_ongoing TINYINT(1) NOT NULL DEFAULT 0,
            color_hex VARCHAR(7) NOT NULL DEFAULT \'#f59e0b\',
            part_name VARCHAR(255) DEFAULT NULL,
            prompt_responses JSON DEFAULT NULL COMMENT \'(DC2Type:json)\',
            created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            INDEX IDX_F981B52EA76ED395 (user_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE part (
            id INT AUTO_INCREMENT NOT NULL,
            user_id INT NOT NULL,
            name VARCHAR(255) NOT NULL,
            trigger_text LONGTEXT DEFAULT NULL,
            needs_text LONGTEXT DEFAULT NULL,
            fears_text LONGTEXT DEFAULT NULL,
            protects_text LONGTEXT DEFAULT NULL,
            color_hex VARCHAR(7) NOT NULL DEFAULT \'#e8a5a0\',
            created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            INDEX IDX_490F70C6A76ED395 (user_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE reflection (
            id INT AUTO_INCREMENT NOT NULL,
            user_id INT NOT NULL,
            prompt_text LONGTEXT NOT NULL,
            response_text LONGTEXT NOT NULL,
            date DATE NOT NULL,
            created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            INDEX IDX_AE2D79A8A76ED395 (user_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE ai_insight (
            id INT AUTO_INCREMENT NOT NULL,
            user_id INT NOT NULL,
            type VARCHAR(30) NOT NULL,
            content LONGTEXT NOT NULL,
            generated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            INDEX IDX_2E14F5DBA76ED395 (user_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE daily_prompt (
            id INT AUTO_INCREMENT NOT NULL,
            prompt_text LONGTEXT NOT NULL,
            sort_order INT NOT NULL,
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('ALTER TABLE chapter ADD CONSTRAINT FK_F981B52EA76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE part ADD CONSTRAINT FK_490F70C6A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE reflection ADD CONSTRAINT FK_AE2D79A8A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE ai_insight ADD CONSTRAINT FK_2E14F5DBA76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');

        // Seed daily prompts
        $prompts = [
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

        foreach ($prompts as $i => $promptText) {
            $escaped = str_replace("'", "''", $promptText);
            $this->addSql("INSERT INTO daily_prompt (prompt_text, sort_order) VALUES ('{$escaped}', {$i})");
        }
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE chapter DROP FOREIGN KEY FK_F981B52EA76ED395');
        $this->addSql('ALTER TABLE part DROP FOREIGN KEY FK_490F70C6A76ED395');
        $this->addSql('ALTER TABLE reflection DROP FOREIGN KEY FK_AE2D79A8A76ED395');
        $this->addSql('ALTER TABLE ai_insight DROP FOREIGN KEY FK_2E14F5DBA76ED395');
        $this->addSql('DROP TABLE `user`');
        $this->addSql('DROP TABLE chapter');
        $this->addSql('DROP TABLE part');
        $this->addSql('DROP TABLE reflection');
        $this->addSql('DROP TABLE ai_insight');
        $this->addSql('DROP TABLE daily_prompt');
    }
}
