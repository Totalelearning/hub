<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\CourseReinforcementAttempt;
use App\Models\CourseReinforcementResponse;
use App\Models\ModuleProgress;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminCourseAnalyticsController extends Controller
{
    public function index(): View
    {
        Gate::authorize('admin-access');

        return view('app.admin-course-analytics', $this->analyticsData());
    }

    public function export(): StreamedResponse
    {
        Gate::authorize('admin-access');

        $data = $this->analyticsData();
        $filename = 'course-analytics-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($data): void {
            $handle = fopen('php://output', 'w');
            if ($handle === false) {
                return;
            }

            $csv = fn (array $row) => fputcsv($handle, $row, ',', '"', '');

            // Summary section
            $csv(['COURSE ANALYTICS REPORT', 'Generated ' . now()->format('Y-m-d H:i')]);
            $csv([]);
            $csv(['SUMMARY']);
            $csv(['Metric', 'Value']);
            $csv(['Published Courses', $data['summary']['total_courses']]);
            $csv(['Total Enrolments', $data['summary']['total_assigned']]);
            $csv(['Total Completed', $data['summary']['total_completed']]);
            $csv(['Overall Completion Rate', $data['summary']['overall_completion_rate'] . '%']);
            $csv(['Knowledge Check Attempts', $data['summary']['total_reinforcement_attempts']]);
            $csv(['Knowledge Checks Passed', $data['summary']['total_passed']]);
            $csv(['Knowledge Checks Failed', $data['summary']['total_failed']]);
            $csv(['Overall Avg Score', ($data['summary']['overall_avg_score'] ?? 'N/A') . ($data['summary']['overall_avg_score'] !== null ? '%' : '')]);

            // Course performance section
            $csv([]);
            $csv(['COURSE PERFORMANCE']);
            $csv(['Course', 'Modules', 'Enrolled', 'Completed', 'In Progress', 'Not Started', 'Completion %', 'Quiz Attempts', 'Passed', 'Failed', 'Pass Rate %', 'Avg Score %']);
            foreach ($data['courses'] as $course) {
                $stats = $course->stats;
                $reinf = $course->reinforcement;
                $csv([
                    $course->title,
                    $course->modules_count,
                    $stats['assigned'],
                    $stats['completed'],
                    $stats['in_progress'],
                    $stats['not_started'],
                    $stats['completion_rate'] . '%',
                    $reinf['total_attempts'],
                    $reinf['passed'],
                    $reinf['failed'],
                    $reinf['pass_rate'] !== null ? $reinf['pass_rate'] . '%' : 'N/A',
                    $reinf['avg_score'] !== null ? $reinf['avg_score'] . '%' : 'N/A',
                ]);
            }

            // Knowledge gap hotspots
            $csv([]);
            $csv(['KNOWLEDGE GAP HOTSPOTS']);
            $csv(['Module', 'Incorrect Answers']);
            foreach ($data['topGapModules'] as $gap) {
                $csv([$gap['module_title'], $gap['incorrect_count']]);
            }

            // Learners needing attention
            $csv([]);
            $csv(['LEARNERS NEEDING ATTENTION']);
            $csv(['Learner', 'Email', 'Course', 'Course Status']);
            foreach ($data['learnersWithGaps'] as $learner) {
                $csv([
                    $learner->name,
                    $learner->email,
                    $learner->course_title,
                    $learner->course_status ?? 'gaps_found',
                ]);
            }

            // Recent knowledge check results
            $csv([]);
            $csv(['RECENT KNOWLEDGE CHECK RESULTS']);
            $csv(['Learner', 'Email', 'Course', 'Score %', 'Result', 'Completed At']);
            foreach ($data['recentAttempts'] as $attempt) {
                $csv([
                    $attempt->user?->name ?? 'Unknown',
                    $attempt->user?->email ?? '',
                    $attempt->course?->title ?? 'Unknown',
                    $attempt->score_percent,
                    $attempt->status === 'completed' ? 'Passed' : 'Gaps Found',
                    $attempt->completed_at?->format('Y-m-d H:i:s') ?? '',
                ]);
            }

            // Per-course learner detail
            $csv([]);
            $csv(['ENROLMENT DETAIL']);
            $csv(['Course', 'Learner', 'Email', 'Status', 'Completed At', 'Reinforcement Status']);

            $enrolments = DB::table('course_user')
                ->join('users', 'users.id', '=', 'course_user.user_id')
                ->join('courses', 'courses.id', '=', 'course_user.course_id')
                ->where('courses.status', 'published')
                ->select(
                    'courses.title as course_title',
                    'users.name',
                    'users.email',
                    'course_user.status',
                    'course_user.completed_at',
                    'course_user.reinforcement_status'
                )
                ->orderBy('courses.title')
                ->orderBy('users.name')
                ->get();

            foreach ($enrolments as $row) {
                $csv([
                    $row->course_title,
                    $row->name,
                    $row->email,
                    $row->status,
                    $row->completed_at ?? '',
                    $row->reinforcement_status ?? '',
                ]);
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    private function analyticsData(): array
    {
        $courses = Course::where('status', 'published')
            ->withCount(['modules', 'assignedUsers'])
            ->with('modules:id,title')
            ->orderBy('title')
            ->get();

        // Pivot data: completion stats per course
        $pivotData = DB::table('course_user')
            ->select(
                'course_id',
                DB::raw("count(*) as total_assigned"),
                DB::raw("count(case when status = 'completed' then 1 end) as total_completed"),
                DB::raw("count(case when status = 'in_progress' then 1 end) as total_in_progress"),
                DB::raw("count(case when status = 'assigned' then 1 end) as total_not_started"),
            )
            ->groupBy('course_id')
            ->get()
            ->keyBy('course_id');

        // Reinforcement stats per course
        $reinforcementStats = CourseReinforcementAttempt::select(
                'course_id',
                DB::raw("count(*) as total_attempts"),
                DB::raw("count(case when status = 'completed' then 1 end) as passed"),
                DB::raw("count(case when status = 'gaps_found' then 1 end) as failed"),
                DB::raw("round(avg(case when status in ('completed','gaps_found') then score_percent end), 1) as avg_score"),
            )
            ->groupBy('course_id')
            ->get()
            ->keyBy('course_id');

        // Gap frequency: which modules get the most wrong answers
        $gapModules = CourseReinforcementResponse::where('is_correct', false)
            ->join('reinforcement_questions', 'reinforcement_questions.id', '=', 'course_reinforcement_responses.reinforcement_question_id')
            ->whereNotNull('reinforcement_questions.remediation_learning_module_id')
            ->select(
                'reinforcement_questions.remediation_learning_module_id',
                DB::raw('count(*) as incorrect_count')
            )
            ->groupBy('reinforcement_questions.remediation_learning_module_id')
            ->orderByDesc('incorrect_count')
            ->limit(10)
            ->get();

        $gapModuleIds = $gapModules->pluck('remediation_learning_module_id');
        $gapModuleNames = DB::table('learning_modules')
            ->whereIn('id', $gapModuleIds)
            ->pluck('title', 'id');

        $topGapModules = $gapModules->map(fn ($row) => [
            'module_id' => $row->remediation_learning_module_id,
            'module_title' => $gapModuleNames[$row->remediation_learning_module_id] ?? 'Unknown',
            'incorrect_count' => $row->incorrect_count,
        ]);

        // Attach stats to courses
        $courses->each(function ($course) use ($pivotData, $reinforcementStats) {
            $pivot = $pivotData->get($course->id);
            $course->setAttribute('stats', [
                'assigned' => $pivot->total_assigned ?? 0,
                'completed' => $pivot->total_completed ?? 0,
                'in_progress' => $pivot->total_in_progress ?? 0,
                'not_started' => $pivot->total_not_started ?? 0,
                'completion_rate' => ($pivot->total_assigned ?? 0) > 0
                    ? round(($pivot->total_completed ?? 0) / $pivot->total_assigned * 100)
                    : 0,
            ]);

            $rStats = $reinforcementStats->get($course->id);
            $course->setAttribute('reinforcement', [
                'total_attempts' => $rStats->total_attempts ?? 0,
                'passed' => $rStats->passed ?? 0,
                'failed' => $rStats->failed ?? 0,
                'avg_score' => $rStats->avg_score ?? null,
                'pass_rate' => ($rStats->total_attempts ?? 0) > 0 && (($rStats->passed ?? 0) + ($rStats->failed ?? 0)) > 0
                    ? round(($rStats->passed ?? 0) / (($rStats->passed ?? 0) + ($rStats->failed ?? 0)) * 100)
                    : null,
            ]);
        });

        // Global summary
        $summary = [
            'total_courses' => $courses->count(),
            'total_assigned' => $pivotData->sum('total_assigned'),
            'total_completed' => $pivotData->sum('total_completed'),
            'overall_completion_rate' => $pivotData->sum('total_assigned') > 0
                ? round($pivotData->sum('total_completed') / $pivotData->sum('total_assigned') * 100)
                : 0,
            'total_reinforcement_attempts' => $reinforcementStats->sum('total_attempts'),
            'total_passed' => $reinforcementStats->sum('passed'),
            'total_failed' => $reinforcementStats->sum('failed'),
            'overall_avg_score' => CourseReinforcementAttempt::whereIn('status', ['completed', 'gaps_found'])->avg('score_percent'),
        ];
        $summary['overall_avg_score'] = $summary['overall_avg_score'] !== null
            ? round($summary['overall_avg_score'], 1)
            : null;

        // Recent reinforcement attempts
        $recentAttempts = CourseReinforcementAttempt::with(['course:id,title', 'user:id,name,email'])
            ->whereIn('status', ['completed', 'gaps_found'])
            ->latest('completed_at')
            ->limit(15)
            ->get();

        // Learners needing attention: have a gaps_found attempt and course is not yet re-completed
        $learnersWithGaps = DB::table('course_user')
            ->where(function ($q) {
                $q->where('reinforcement_status', 'gaps_found')
                  ->orWhere(function ($q2) {
                      $q2->where('course_user.status', 'in_progress')
                         ->whereExists(function ($sub) {
                             $sub->select(DB::raw(1))
                                 ->from('course_reinforcement_attempts')
                                 ->whereColumn('course_reinforcement_attempts.course_id', 'course_user.course_id')
                                 ->whereColumn('course_reinforcement_attempts.user_id', 'course_user.user_id')
                                 ->where('course_reinforcement_attempts.status', 'gaps_found');
                         });
                  });
            })
            ->join('users', 'users.id', '=', 'course_user.user_id')
            ->join('courses', 'courses.id', '=', 'course_user.course_id')
            ->select('users.id as user_id', 'users.name', 'users.email', 'courses.title as course_title', 'course_user.course_id', 'course_user.status as course_status')
            ->orderBy('users.name')
            ->limit(20)
            ->get();

        return [
            'courses' => $courses,
            'summary' => $summary,
            'topGapModules' => $topGapModules,
            'recentAttempts' => $recentAttempts,
            'learnersWithGaps' => $learnersWithGaps,
        ];
    }
}
