<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LearningModuleSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $modules = [
            [
                'title' => 'Mastering Prompt Basics for Study',
                'description' => 'Learn how to write clear prompts that help you summarize chapters, generate quizzes, and break hard topics into simple steps.',
                'topic' => 'ai-literacy',
                'difficulty' => 'beginner',
                'target_roles' => ['manager', 'specialist'],
                'is_required' => false,
                'compliance_area' => null,
                'refresh_interval_days' => null,
                'status' => 'published',
                'source_type' => 'manual',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'title' => 'Time Blocking for Busy Students',
                'description' => 'Build a weekly schedule with deep-work blocks, revision windows, and buffer time so you can stay consistent without burnout.',
                'topic' => 'productivity',
                'difficulty' => 'beginner',
                'target_roles' => ['new-starter', 'specialist'],
                'is_required' => false,
                'compliance_area' => null,
                'refresh_interval_days' => null,
                'status' => 'published',
                'source_type' => 'manual',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'title' => 'Memory Techniques That Actually Stick',
                'description' => 'Use active recall, spaced repetition, and simple mnemonic patterns to retain definitions, formulas, and key concepts for exams.',
                'topic' => 'learning-science',
                'difficulty' => 'intermediate',
                'target_roles' => null,
                'is_required' => false,
                'compliance_area' => null,
                'refresh_interval_days' => 30,
                'status' => 'published',
                'source_type' => 'manual',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'title' => 'Rapid Reading and Note Compression',
                'description' => 'Convert long chapters into short, high-signal notes using the 3-layer method: headline, core idea, and applied example.',
                'topic' => 'productivity',
                'difficulty' => 'intermediate',
                'target_roles' => ['new-starter'],
                'is_required' => false,
                'compliance_area' => null,
                'refresh_interval_days' => null,
                'status' => 'published',
                'source_type' => 'manual',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'title' => 'Exam-Day Calm and Focus Routine',
                'description' => 'Practice a lightweight pre-exam routine with breathing, checklist review, and quick confidence cues to reduce stress.',
                'topic' => 'wellbeing',
                'difficulty' => 'beginner',
                'target_roles' => null,
                'is_required' => false,
                'compliance_area' => null,
                'refresh_interval_days' => 90,
                'status' => 'published',
                'source_type' => 'manual',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'title' => 'Build Better Questions with AI',
                'description' => 'Turn any textbook topic into layered practice questions from easy to advanced so you can test understanding, not memorization.',
                'topic' => 'ai-literacy',
                'difficulty' => 'advanced',
                'target_roles' => ['manager'],
                'is_required' => true,
                'compliance_area' => 'ai-safety',
                'refresh_interval_days' => 180,
                'status' => 'published',
                'source_type' => 'manual',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        foreach ($modules as $module) {
            if (array_key_exists('target_roles', $module) && is_array($module['target_roles'])) {
                $module['target_roles'] = json_encode($module['target_roles'], JSON_THROW_ON_ERROR);
            }

            DB::table('learning_modules')->updateOrInsert(
                ['title' => $module['title']],
                $module,
            );
        }
    }
}
