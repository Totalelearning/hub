<?php

namespace App\Services;

use App\Models\Course;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CourseCompletionService
{
    /**
     * Check if a user has completed all modules in a course.
     * If so, mark the course as completed on the pivot.
     *
     * Returns true if the course was newly marked as completed.
     */
    public function checkAndMarkComplete(Course $course, User $user): bool
    {
        // Check the current pivot status
        $pivot = DB::table('course_user')
            ->where('course_id', $course->id)
            ->where('user_id', $user->id)
            ->first();

        if (! $pivot) {
            return false; // User not assigned to this course
        }

        if ($pivot->status === 'completed') {
            return false; // Already completed
        }

        if (! $course->isCompletedByUser($user)) {
            // Not all modules complete yet — mark as in_progress if currently assigned
            if ($pivot->status === 'assigned') {
                DB::table('course_user')
                    ->where('course_id', $course->id)
                    ->where('user_id', $user->id)
                    ->update(['status' => 'in_progress']);
            }

            return false;
        }

        // All modules completed — mark course as completed
        DB::table('course_user')
            ->where('course_id', $course->id)
            ->where('user_id', $user->id)
            ->update([
                'status' => 'completed',
                'completed_at' => now(),
            ]);

        // Award gamification XP, update streak, and evaluate badges
        $gamification = app(\App\Services\GamificationService::class);
        $gamification->awardCourseCompletion($user, $course);
        $gamification->recordActivity($user);
        $gamification->evaluateBadges($user);

        return true;
    }

    /**
     * Check all courses assigned to a user and mark completed ones.
     */
    public function syncAllForUser(User $user): int
    {
        $courses = Course::query()
            ->whereHas('assignedUsers', fn ($q) => $q->where('user_id', $user->id))
            ->with('modules:id')
            ->get();

        $newlyCompleted = 0;

        foreach ($courses as $course) {
            if ($this->checkAndMarkComplete($course, $user)) {
                $newlyCompleted++;
            }
        }

        return $newlyCompleted;
    }
}
