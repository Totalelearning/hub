<?php

namespace App\Http\Controllers;

use App\Models\CourseReinforcementAttempt;
use App\Models\CourseReinforcementResponse;
use App\Models\ModuleProgress;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CourseReinforcementController extends Controller
{
    public function show(string $token): View
    {
        $attempt = CourseReinforcementAttempt::where('token', $token)
            ->with(['course.modules', 'user'])
            ->firstOrFail();

        abort_if(auth()->id() !== $attempt->user_id, 403);
        abort_if(in_array($attempt->status, ['completed', 'gaps_found']), 410, 'This knowledge check has already been completed.');

        // Mark as started
        if (! $attempt->started_at) {
            $attempt->update([
                'started_at' => now(),
                'status' => 'in_progress',
            ]);
        }

        // Gather all approved questions across the course's modules
        $questionSets = $attempt->course->approvedQuestionSets();

        $questions = $questionSets->flatMap(function ($set) {
            return $set->questions->map(function ($question) use ($set) {
                $question->setAttribute('source_module_title', $set->module?->title ?? 'Unknown module');
                return $question;
            });
        })->values();

        return view('app.course-reinforcement-show', [
            'attempt' => $attempt,
            'course' => $attempt->course,
            'questions' => $questions,
        ]);
    }

    public function submit(Request $request, string $token): RedirectResponse
    {
        $attempt = CourseReinforcementAttempt::where('token', $token)
            ->with(['course.modules'])
            ->firstOrFail();

        abort_if(auth()->id() !== $attempt->user_id, 403);
        abort_if(in_array($attempt->status, ['completed', 'gaps_found']), 410);

        $questionSets = $attempt->course->approvedQuestionSets();
        $allQuestions = $questionSets->flatMap(fn ($set) => $set->questions)->keyBy('id');

        $answers = $request->input('answers', []);

        $totalQuestions = $allQuestions->count();
        $correctCount = 0;
        $incorrectModuleIds = [];

        foreach ($allQuestions as $question) {
            $selectedAnswer = $answers[$question->id] ?? null;
            $isCorrect = $selectedAnswer !== null && strtoupper($selectedAnswer) === strtoupper($question->correct_answer);

            if ($isCorrect) {
                $correctCount++;
            } elseif ($question->remediation_learning_module_id) {
                $incorrectModuleIds[] = $question->remediation_learning_module_id;
            }

            CourseReinforcementResponse::updateOrCreate(
                [
                    'course_reinforcement_attempt_id' => $attempt->id,
                    'reinforcement_question_id' => $question->id,
                ],
                [
                    'user_id' => $attempt->user_id,
                    'selected_answer' => $selectedAnswer,
                    'is_correct' => $isCorrect,
                    'answered_at' => now(),
                ]
            );
        }

        $scorePercent = $totalQuestions > 0 ? round(($correctCount / $totalQuestions) * 100, 2) : 0;
        $incorrectModuleIds = array_unique($incorrectModuleIds);
        $hasGaps = count($incorrectModuleIds) > 0;

        // Update the attempt
        $attempt->update([
            'status' => $hasGaps ? 'gaps_found' : 'completed',
            'completed_at' => now(),
            'score_percent' => $scorePercent,
            'metadata' => [
                'total_questions' => $totalQuestions,
                'correct_count' => $correctCount,
                'incorrect_module_ids' => $incorrectModuleIds,
            ],
        ]);

        // If gaps found, reset progress on the failing modules and update course status
        if ($hasGaps) {
            foreach ($incorrectModuleIds as $moduleId) {
                ModuleProgress::query()
                    ->where('user_id', $attempt->user_id)
                    ->where('learning_module_id', $moduleId)
                    ->update([
                        'status' => 'not_started',
                        'percent_complete' => 0,
                        'completed_at' => null,
                    ]);
            }

            // Reset course to in_progress — once they re-complete the failed modules,
            // ProgressService will mark the course completed again and the cycle restarts
            DB::table('course_user')
                ->where('course_id', $attempt->course_id)
                ->where('user_id', $attempt->user_id)
                ->update([
                    'status' => 'in_progress',
                    'completed_at' => null,
                    'reinforcement_sent_at' => null,
                    'reinforcement_status' => 'gaps_found',
                ]);
        } else {
            // Passed — reset the clock so they get quizzed again after the delay.
            // completed_at resets to now so the delay counts from today.
            DB::table('course_user')
                ->where('course_id', $attempt->course_id)
                ->where('user_id', $attempt->user_id)
                ->update([
                    'completed_at' => now(),
                    'reinforcement_sent_at' => null,
                    'reinforcement_status' => 'passed',
                ]);
        }

        return redirect()
            ->route('course-reinforcement.result', ['token' => $token]);
    }

    public function result(string $token): View
    {
        $attempt = CourseReinforcementAttempt::where('token', $token)
            ->with(['course', 'responses.question.remediationModule'])
            ->firstOrFail();

        abort_if(auth()->id() !== $attempt->user_id, 403);
        abort_unless(in_array($attempt->status, ['completed', 'gaps_found']), 404);

        $incorrectResponses = $attempt->responses->where('is_correct', false);
        $reassignedModules = $incorrectResponses
            ->map(fn ($r) => $r->question?->remediationModule)
            ->filter()
            ->unique('id')
            ->values();

        return view('app.course-reinforcement-result', [
            'attempt' => $attempt,
            'course' => $attempt->course,
            'reassignedModules' => $reassignedModules,
        ]);
    }
}
