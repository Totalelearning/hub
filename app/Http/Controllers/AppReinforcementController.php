<?php

namespace App\Http\Controllers;

use App\Models\ReinforcementTouchpoint;
use App\Models\LearningModule;
use App\Services\ReinforcementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AppReinforcementController extends Controller
{
    public function show(ReinforcementTouchpoint $touchpoint): View
    {
        $user = auth()->user();
        abort_unless($user, 403);
        abort_unless($touchpoint->user_id === $user->id, 403);

        $touchpoint->loadMissing([
            'module',
            'reinforcementQuestionSet.questions.remediationModule',
            'responses',
        ]);

        $questionSet = $touchpoint->reinforcementQuestionSet;
        $incorrectCount = (int) (($touchpoint->metadata['last_incorrect_count'] ?? 0));
        $remediationModuleIds = collect($touchpoint->metadata['last_remediation_module_ids'] ?? [])
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();
        $remediationModules = $remediationModuleIds->isNotEmpty()
            ? LearningModule::query()->whereIn('id', $remediationModuleIds->all())->orderBy('title')->get(['id', 'title', 'source_type'])
            : collect();
        $questionsMissingRemediation = $questionSet
            ? $questionSet->questions->filter(fn ($question) => $question->remediation_learning_module_id === null)->count()
            : 0;

        return view('app.reinforcement-show', [
            'touchpoint' => $touchpoint,
            'questionSet' => $questionSet,
            'existingResponses' => $touchpoint->responses->keyBy('reinforcement_question_id'),
            'incorrectCount' => $incorrectCount,
            'remediationModules' => $remediationModules,
            'questionsMissingRemediation' => $questionsMissingRemediation,
        ]);
    }

    public function submit(Request $request, ReinforcementTouchpoint $touchpoint, ReinforcementService $reinforcement): RedirectResponse
    {
        $user = auth()->user();
        abort_unless($user, 403);
        abort_unless($touchpoint->user_id === $user->id, 403);

        $touchpoint->loadMissing('reinforcementQuestionSet.questions');
        $questionSet = $touchpoint->reinforcementQuestionSet;
        abort_unless($questionSet !== null && $questionSet->questions->isNotEmpty(), 404);

        $rules = [];
        foreach ($questionSet->questions as $question) {
            $rules['answers.'.$question->id] = ['required', 'in:A,B,C,D'];
        }

        $validated = $request->validate($rules);
        $result = $reinforcement->submitAnswers($touchpoint, $user, $validated['answers'] ?? []);

        if (($result['status'] ?? null) === 'completed') {
            return redirect()
                ->route('app.reminders')
                ->with('status', 'Reinforcement proof recorded.');
        }

        return redirect()
            ->route('app.reinforcement.show', ['touchpoint' => $touchpoint->id])
            ->with('status', 'Answers recorded. Extra learning has been assigned where needed.');
    }

    public function complete(ReinforcementTouchpoint $touchpoint, ReinforcementService $reinforcement): RedirectResponse
    {
        $user = auth()->user();
        abort_unless($user, 403);

        $reinforcement->completeForUser($touchpoint, $user);

        return redirect()
            ->back()
            ->with('status', 'Reinforcement proof recorded.');
    }
}
