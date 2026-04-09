<?php

namespace App\Services;

use App\Models\LearningAsset;
use App\Models\LearningModule;
use App\Models\ReinforcementQuestion;
use App\Models\ReinforcementQuestionSet;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class ReinforcementQuestionDraftService
{
    public function draftForModule(LearningModule $module): ReinforcementQuestionSet
    {
        $asset = $module->latestScormAsset();
        $sourceText = $this->sourceText($module, $asset);

        $questionSet = ReinforcementQuestionSet::query()
            ->where('learning_module_id', $module->id)
            ->whereIn('status', ['draft', 'in_review'])
            ->latest('id')
            ->first();

        if (! $questionSet) {
            $questionSet = new ReinforcementQuestionSet([
                'learning_module_id' => $module->id,
            ]);
        }

        $questionSet->fill([
            'learning_asset_id' => $asset?->id,
            'status' => 'draft',
            'generation_mode' => 'ai_draft',
            'title' => $module->title.' reinforcement draft',
            'summary' => 'AI-drafted reinforcement questions pending admin review.',
            'draft_source_excerpt' => Str::limit($sourceText, 500),
            'generated_at' => now(),
            'reviewed_at' => null,
            'reviewed_by' => null,
            'metadata' => [
                'draft_origin' => 'local_heuristic_ai_draft',
                'source_type' => $module->source_type ?? 'manual',
                'question_count' => 3,
            ],
        ]);
        $questionSet->save();

        $questionSet->questions()->delete();

        foreach ($this->draftQuestions($module, $sourceText) as $index => $question) {
            $questionSet->questions()->create([
                'position' => $index + 1,
                'question_type' => 'multiple_choice',
                'question_text' => $question['question_text'],
                'options' => $question['options'],
                'correct_answer' => $question['correct_answer'],
                'explanation' => $question['explanation'],
                'status' => 'draft',
                'metadata' => [
                    'draft_origin' => 'local_heuristic_ai_draft',
                ],
            ]);
        }

        return $questionSet->fresh(['questions', 'learningAsset']);
    }

    /**
     * @return array<int, array{question_text:string, options:array<string,string>, correct_answer:string, explanation:string}>
     */
    private function draftQuestions(LearningModule $module, string $sourceText): array
    {
        $topic = trim((string) ($module->topic ?: $module->compliance_area ?: 'the core topic'));
        $title = trim((string) $module->title);
        $summary = trim((string) Str::limit(preg_replace('/\s+/', ' ', $sourceText), 180, '...'));

        return [
            [
                'question_text' => 'Which answer best describes the main focus learners should remember from '.$title.'?',
                'options' => [
                    'A' => 'Apply the key practice in '.$topic.' when making decisions.',
                    'B' => 'Ignore the module unless a manager asks about it.',
                    'C' => 'Treat the content as optional reference only.',
                    'D' => 'Escalate every decision without checking the module guidance.',
                ],
                'correct_answer' => 'A',
                'explanation' => 'This draft assumes the module reinforces practical decision-making in '.$topic.'. Admin should review the wording before approval.',
            ],
            [
                'question_text' => 'A learner is unsure what to do after completing '.$title.'. Which response best matches the intended reinforcement behavior?',
                'options' => [
                    'A' => 'Use the module guidance in practice and revisit the key decision points.',
                    'B' => 'Wait until the next annual course and ignore follow-up checks.',
                    'C' => 'Skip reminders because completion is enough proof.',
                    'D' => 'Delete previous notes to avoid confusion.',
                ],
                'correct_answer' => 'A',
                'explanation' => 'This draft question is designed to test retention and application, not just recall. Admin review should align it with the real learning objective.',
            ],
            [
                'question_text' => 'Which summary best reflects the material used to draft these reinforcement questions?',
                'options' => [
                    'A' => $summary !== '' ? $summary : 'Module title, description, and any extracted text from the SCORM package.',
                    'B' => 'Only the learner event log.',
                    'C' => 'Only the admin user profile.',
                    'D' => 'A random unrelated module.',
                ],
                'correct_answer' => 'A',
                'explanation' => 'This question helps the reviewer see what source context the draft was generated from and should be replaced with a sharper knowledge check before approval.',
            ],
        ];
    }

    private function sourceText(LearningModule $module, ?LearningAsset $asset): string
    {
        $parts = collect([
            $module->title,
            $module->description,
            $module->topic,
            $module->compliance_area,
            $module->content_text,
            $asset?->processing_metadata['manifest_title'] ?? null,
            $asset?->processing_metadata['manifest_description'] ?? null,
            $asset?->processing_metadata['toc_text'] ?? null,
        ])->filter(fn ($value) => filled($value));

        return $parts->implode("\n\n");
    }
}
