<?php

namespace App\Services;

readonly class GameQuestionService
{
    public function generate(string $gameType, array $settings = []): array
    {
        return match ($gameType) {
            'math-challenge' => $this->mathChallenge($settings),
            'vocabulary-race' => $this->vocabularyRace($settings),
            'quiz-battle' => $this->quizBattle($settings),
            'memory-game' => $this->memoryGame($settings),
            default => [],
        };
    }

    private function mathChallenge(array $settings): array
    {
        $difficulty = $settings['difficulty'] ?? 'medium';
        $numQuestions = (int) ($settings['numQuestions'] ?? 10);
        $operations = $settings['operations'] ?? ['+', '-', '×', '÷'];

        $max = match ($difficulty) {
            'easy' => 10,
            'medium' => 50,
            'hard' => 100,
        };

        $questions = [];

        foreach (range(1, $numQuestions) as $i) {
            $op = $operations[array_rand($operations)];

            match ($op) {
                '+' => $this->generateBinaryOp($questions, $i, $max, '+', false, $difficulty),
                '-' => $this->generateBinaryOp($questions, $i, $max, '-', true, $difficulty),
                '×' => $this->generateBinaryOp($questions, $i, $max > 10 ? 12 : 5, '×', false, $difficulty),
                '÷' => $this->generateDivision($questions, $i, $max, $difficulty),
            };
        }

        return $questions;
    }

    private function generateBinaryOp(array &$questions, int $index, int $max, string $symbol, bool $noNegative = false, string $difficulty = 'medium'): void
    {
        $a = random_int(1, $max);
        $b = random_int(1, $max);

        if ($noNegative && $symbol === '-' && $a < $b) {
            [$a, $b] = [$b, $a];
        }

        $answer = match ($symbol) {
            '+' => $a + $b,
            '-' => $a - $b,
            '×' => $a * $b,
        };

        $questions[] = [
            'id' => $index,
            'type' => 'math',
            'question' => "{$a} {$symbol} {$b} = ?",
            'answer' => (string) $answer,
            'difficulty' => $difficulty,
        ];
    }

    private function generateDivision(array &$questions, int $index, int $max, string $difficulty = 'medium'): void
    {
        $b = random_int(1, min(12, $max));
        $answer = random_int(1, max(1, (int) floor($max / max(1, $b))));
        $a = $b * $answer;

        $questions[] = [
            'id' => $index,
            'type' => 'math',
            'question' => "{$a} ÷ {$b} = ?",
            'answer' => (string) $answer,
            'difficulty' => $difficulty,
        ];
    }

    private function vocabularyRace(array $settings): array
    {
        $wordList = $settings['wordList'] ?? [];
        $rounds = (int) ($settings['rounds'] ?? 3);
        $timeLimit = (int) ($settings['timeLimit'] ?? 60);

        $words = is_array($wordList) ? $wordList : explode("\n", (string) $wordList);
        $words = array_values(array_filter(array_map('trim', $words)));

        if (empty($words)) {
            return [];
        }

        $questions = [];
        $counter = 1;

        foreach (range(1, $rounds) as $round) {
            shuffle($words);

            foreach ($words as $word) {
                $questions[] = [
                    'id' => $counter++,
                    'type' => 'vocabulary',
                    'round' => $round,
                    'prompt' => 'Type the word shown on the screen.',
                    'word' => $word,
                    'answer' => $word,
                    'timeLimit' => $timeLimit,
                ];
            }
        }

        return $questions;
    }

    private function quizBattle(array $settings): array
    {
        $difficulty = $settings['difficulty'] ?? 'medium';
        $numQuestions = (int) ($settings['numQuestions'] ?? 10);
        $categories = $settings['categories'] ?? [];

        $bank = [
            ['category' => 'Science', 'question' => 'What planet is known as the Red Planet?', 'options' => ['Venus', 'Mars', 'Jupiter', 'Saturn'], 'answer' => 'Mars'],
            ['category' => 'Science', 'question' => 'What gas do plants absorb from the atmosphere?', 'options' => ['Oxygen', 'Carbon Dioxide', 'Nitrogen', 'Hydrogen'], 'answer' => 'Carbon Dioxide'],
            ['category' => 'Science', 'question' => 'What is the boiling point of water?', 'options' => ['90°C', '100°C', '110°C', '120°C'], 'answer' => '100°C'],
            ['category' => 'Science', 'question' => 'What part of the cell contains DNA?', 'options' => ['Ribosome', 'Nucleus', 'Cytoplasm', 'Mitochondria'], 'answer' => 'Nucleus'],
            ['category' => 'History', 'question' => 'In what year did World War II end?', 'options' => ['1943', '1944', '1945', '1946'], 'answer' => '1945'],
            ['category' => 'History', 'question' => 'Who was the first President of the United States?', 'options' => ['Thomas Jefferson', 'George Washington', 'Abraham Lincoln', 'John Adams'], 'answer' => 'George Washington'],
            ['category' => 'History', 'question' => 'Which empire built the Colosseum?', 'options' => ['Greek', 'Roman', 'Ottoman', 'Byzantine'], 'answer' => 'Roman'],
            ['category' => 'History', 'question' => 'The Titanic sank in which year?', 'options' => ['1910', '1912', '1914', '1916'], 'answer' => '1912'],
            ['category' => 'Math', 'question' => 'What is the square root of 64?', 'options' => ['6', '7', '8', '9'], 'answer' => '8'],
            ['category' => 'Math', 'question' => 'What is 15% of 200?', 'options' => ['20', '25', '30', '35'], 'answer' => '30'],
            ['category' => 'Math', 'question' => 'How many sides does a hexagon have?', 'options' => ['5', '6', '7', '8'], 'answer' => '6'],
            ['category' => 'English', 'question' => 'What is the plural form of "child"?', 'options' => ['Childs', 'Children', 'Childrens', 'Childes'], 'answer' => 'Children'],
            ['category' => 'English', 'question' => 'Which word is a synonym for "happy"?', 'options' => ['Sad', 'Joyful', 'Angry', 'Tired'], 'answer' => 'Joyful'],
            ['category' => 'English', 'question' => 'What type of word is "quickly"?', 'options' => ['Noun', 'Verb', 'Adjective', 'Adverb'], 'answer' => 'Adverb'],
            ['category' => 'Geography', 'question' => 'What is the largest ocean on Earth?', 'options' => ['Atlantic', 'Indian', 'Pacific', 'Arctic'], 'answer' => 'Pacific'],
            ['category' => 'Geography', 'question' => 'Which country has the most people?', 'options' => ['USA', 'India', 'China', 'Indonesia'], 'answer' => 'India'],
            ['category' => 'Geography', 'question' => 'What is the capital of France?', 'options' => ['London', 'Berlin', 'Paris', 'Madrid'], 'answer' => 'Paris'],
            ['category' => 'Art', 'question' => 'Who painted the Mona Lisa?', 'options' => ['Van Gogh', 'Picasso', 'Da Vinci', 'Rembrandt'], 'answer' => 'Da Vinci'],
            ['category' => 'Art', 'question' => 'What are the three primary colors?', 'options' => ['Red, Green, Blue', 'Red, Yellow, Blue', 'Cyan, Magenta, Yellow', 'Orange, Purple, Green'], 'answer' => 'Red, Yellow, Blue'],
            ['category' => 'Art', 'question' => 'Which art movement did Salvador Dalí belong to?', 'options' => ['Impressionism', 'Cubism', 'Surrealism', 'Realism'], 'answer' => 'Surrealism'],
        ];

        $filtered = $bank;

        if (! empty($categories)) {
            $filtered = array_values(array_filter($bank, fn ($q) => in_array($q['category'], $categories, true)));
        }

        shuffle($filtered);

        $selected = array_slice($filtered, 0, min($numQuestions, count($filtered)));

        $questions = [];
        foreach ($selected as $i => $q) {
            $questions[] = [
                'id' => $i + 1,
                'type' => 'quiz',
                'category' => $q['category'],
                'question' => $q['question'],
                'options' => $q['options'],
                'answer' => $q['answer'],
                'difficulty' => $difficulty,
            ];
        }

        return $questions;
    }

    private function memoryGame(array $settings): array
    {
        $theme = $settings['theme'] ?? 'animals';
        $gridSize = (int) ($settings['gridSize'] ?? 4);
        $pairs = (int) ($settings['pairs'] ?? ($gridSize * $gridSize / 2));

        $themes = [
            'animals' => ['🐶', '🐱', '🐭', '🐹', '🐰', '🦊', '🐻', '🐼', '🐨', '🐯', '🦁', '🐮'],
            'space' => ['🚀', '🛸', '🌍', '🌙', '⭐', '🪐', '☄️', '👽', '🛰️', '🌌', '🔭', '👨‍🚀'],
            'nature' => ['🌳', '🌲', '🌵', '🌷', '🌻', '🍄', '🌿', '🍀', '🌺', '🍁', '🌾', '🌴'],
            'sports' => ['⚽', '🏀', '🏈', '⚾', '🎾', '🏐', '🏓', '🏸', '🥅', '⛳', '🎱', '🏉'],
            'music' => ['🎵', '🎶', '🎸', '🎹', '🥁', '🎷', '🎺', '🎻', '🪕', '🎼', '🎤', '🎧'],
        ];

        $icons = $themes[$theme] ?? $themes['animals'];
        shuffle($icons);

        $selected = array_slice($icons, 0, $pairs);
        $cards = [];

        foreach ($selected as $index => $icon) {
            $cards[] = ['id' => $index * 2 + 1, 'pairId' => $index, 'icon' => $icon];
            $cards[] = ['id' => $index * 2 + 2, 'pairId' => $index, 'icon' => $icon];
        }

        shuffle($cards);

        return [
            'theme' => $theme,
            'gridSize' => $gridSize,
            'pairs' => $pairs,
            'cards' => $cards,
        ];
    }
}
