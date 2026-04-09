<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\CourseReinforcementAttempt;
use App\Models\CourseReinforcementResponse;
use App\Models\User;
use App\Models\UserPreference;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DemoComplianceDataSeeder extends Seeder
{
    /**
     * New teams with roles to add variety to the compliance/analytics reports.
     */
    private const NEW_TEAMS = [
        'IT & Digital Services' => [
            'IT Manager',
            'Network Technician',
            'Digital Learning Coordinator',
            'Data Officer',
        ],
        'Finance & Operations' => [
            'Finance Manager',
            'Accounts Assistant',
            'Operations Coordinator',
            'Procurement Officer',
        ],
        'Student Services' => [
            'Student Services Manager',
            'Admissions Officer',
            'Careers Advisor',
            'Student Welfare Officer',
        ],
        'Facilities & Estates' => [
            'Facilities Manager',
            'Site Supervisor',
            'Caretaker',
            'Cleaning Supervisor',
        ],
        'HR & People' => [
            'HR Manager',
            'HR Administrator',
            'Recruitment Officer',
            'Training Coordinator',
        ],
    ];

    /**
     * New user names for the additional teams.
     */
    private const NEW_USERS = [
        // IT & Digital Services (6)
        ['name' => 'Aisha Mahmood', 'team' => 'IT & Digital Services'],
        ['name' => 'Craig Henderson', 'team' => 'IT & Digital Services'],
        ['name' => 'Deepa Nair', 'team' => 'IT & Digital Services'],
        ['name' => 'Ethan Wallace', 'team' => 'IT & Digital Services'],
        ['name' => 'Fiona Gallagher', 'team' => 'IT & Digital Services'],
        ['name' => 'Graham Yates', 'team' => 'IT & Digital Services'],

        // Finance & Operations (5)
        ['name' => 'Hannah Kirby', 'team' => 'Finance & Operations'],
        ['name' => 'Ivan Petrov', 'team' => 'Finance & Operations'],
        ['name' => 'Julia Whitmore', 'team' => 'Finance & Operations'],
        ['name' => 'Kevin Doyle', 'team' => 'Finance & Operations'],
        ['name' => 'Laura Simmons', 'team' => 'Finance & Operations'],

        // Student Services (6)
        ['name' => 'Marcus Taylor', 'team' => 'Student Services'],
        ['name' => 'Natasha Okonkwo', 'team' => 'Student Services'],
        ['name' => 'Oliver Grant', 'team' => 'Student Services'],
        ['name' => 'Priya Sharma', 'team' => 'Student Services'],
        ['name' => 'Rachel Fleming', 'team' => 'Student Services'],
        ['name' => 'Stephen Byrne', 'team' => 'Student Services'],

        // Facilities & Estates (5)
        ['name' => 'Tanya Woods', 'team' => 'Facilities & Estates'],
        ['name' => 'Usman Chaudhry', 'team' => 'Facilities & Estates'],
        ['name' => 'Victoria Hale', 'team' => 'Facilities & Estates'],
        ['name' => 'Warren Frost', 'team' => 'Facilities & Estates'],
        ['name' => 'Yolanda Bates', 'team' => 'Facilities & Estates'],

        // HR & People (5)
        ['name' => 'Zara Iqbal', 'team' => 'HR & People'],
        ['name' => 'Andrew Cromwell', 'team' => 'HR & People'],
        ['name' => 'Bethany Marsh', 'team' => 'HR & People'],
        ['name' => 'Christopher Owen', 'team' => 'HR & People'],
        ['name' => 'Diana Reeves', 'team' => 'HR & People'],

        // Extra users for existing teams to boost numbers
        ['name' => 'Edward Sutton', 'team' => 'Teaching Staff'],
        ['name' => 'Freya Thompson', 'team' => 'Teaching Staff'],
        ['name' => 'Gareth Lloyd', 'team' => 'Teaching Staff'],
        ['name' => 'Helena Cross', 'team' => 'Senior Leadership Team (SLT)'],
        ['name' => 'Isaac Newton-Hall', 'team' => 'Senior Leadership Team (SLT)'],
        ['name' => 'Jasmine Okoro', 'team' => 'Teaching Support Staff'],
        ['name' => 'Kieran Doyle', 'team' => 'Teaching Support Staff'],
        ['name' => 'Leanne Marshall', 'team' => 'Safeguarding & Pastoral Team'],
        ['name' => 'Niall Brennan', 'team' => 'Safeguarding & Pastoral Team'],
    ];

    /**
     * Completion probability by team — gives varied completion rates across teams.
     * Higher = more completions for realistic variance.
     */
    private const TEAM_COMPLETION_RATES = [
        'Senior Leadership Team (SLT)' => 0.85,
        'Teaching Staff' => 0.65,
        'Teaching Support Staff' => 0.55,
        'Safeguarding & Pastoral Team' => 0.80,
        'IT & Digital Services' => 0.75,
        'Finance & Operations' => 0.45,
        'Student Services' => 0.60,
        'Facilities & Estates' => 0.35,
        'HR & People' => 0.70,
    ];

    public function run(): void
    {
        $courses = Course::where('status', 'published')->get();

        if ($courses->isEmpty()) {
            $this->command->warn('No published courses found. Skipping.');

            return;
        }

        // Step 1: Create new users with preferences
        $newUserIds = $this->createUsers();
        $this->command->info('Created ' . count($newUserIds) . ' new users across ' . count(self::NEW_TEAMS) + 4 . ' teams.');

        // Step 2: Enrol new users in courses
        $this->enrolUsers($newUserIds, $courses);

        // Step 3: Update existing "assigned" enrolments with varied completions
        $this->progressEnrolments($courses);

        // Step 4: Create reinforcement attempts for completed users
        $this->createReinforcementAttempts($courses);

        $this->command->info('Demo compliance data seeded successfully.');
    }

    private function createUsers(): array
    {
        $userIds = [];

        foreach (self::NEW_USERS as $userData) {
            $email = Str::slug($userData['name'], '.') . '@totalelearning.local';
            $team = $userData['team'];
            $roles = self::NEW_TEAMS[$team] ?? PrototypeDemoSeeder::TEAM_ROLE_GROUPS[$team] ?? ['Staff Member'];
            $role = $roles[array_rand($roles)];

            $user = User::updateOrCreate(
                ['email' => $email],
                [
                    'name' => $userData['name'],
                    'password' => Hash::make('password'),
                ]
            );

            UserPreference::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'team' => $team,
                    'role' => $role,
                    'topics' => json_encode(['compliance', 'safety']),
                    'goal' => 'Complete mandatory training and stay up to date',
                    'difficulty' => 'intermediate',
                ]
            );

            $userIds[] = $user->id;
        }

        return $userIds;
    }

    private function enrolUsers(array $userIds, $courses): void
    {
        foreach ($userIds as $userId) {
            foreach ($courses as $course) {
                // Skip randomly ~15% of courses so not everyone is enrolled in everything
                if (mt_rand(1, 100) <= 15) {
                    continue;
                }

                $exists = DB::table('course_user')
                    ->where('course_id', $course->id)
                    ->where('user_id', $userId)
                    ->exists();

                if (! $exists) {
                    DB::table('course_user')->insert([
                        'course_id' => $course->id,
                        'user_id' => $userId,
                        'status' => 'assigned',
                        'created_at' => now()->subDays(mt_rand(7, 60)),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }

    private function progressEnrolments($courses): void
    {
        // Get all assigned/in_progress enrolments with their team
        $enrolments = DB::table('course_user')
            ->join('users', 'users.id', '=', 'course_user.user_id')
            ->leftJoin('user_preferences', 'user_preferences.user_id', '=', 'users.id')
            ->whereIn('course_user.status', ['assigned', 'in_progress'])
            ->select('course_user.id', 'course_user.course_id', 'course_user.user_id', 'user_preferences.team')
            ->get();

        foreach ($enrolments as $enrolment) {
            $team = $enrolment->team ?? 'Teaching Staff';
            $completionRate = self::TEAM_COMPLETION_RATES[$team] ?? 0.5;
            $roll = mt_rand(1, 100) / 100;

            if ($roll <= $completionRate) {
                // Completed
                $completedAt = now()->subDays(mt_rand(1, 30));
                DB::table('course_user')
                    ->where('id', $enrolment->id)
                    ->update([
                        'status' => 'completed',
                        'completed_at' => $completedAt,
                        'updated_at' => $completedAt,
                    ]);
            } elseif ($roll <= $completionRate + 0.20) {
                // In progress
                DB::table('course_user')
                    ->where('id', $enrolment->id)
                    ->update([
                        'status' => 'in_progress',
                        'updated_at' => now()->subDays(mt_rand(1, 14)),
                    ]);
            }
            // else stays as "assigned" (not started)
        }
    }

    private function createReinforcementAttempts($courses): void
    {
        // Get all completed enrolments
        $completedEnrolments = DB::table('course_user')
            ->where('status', 'completed')
            ->select('course_id', 'user_id', 'completed_at')
            ->get();

        // Get question IDs per course (via course_module -> reinforcement_question_sets -> reinforcement_questions)
        $questionsByCourse = [];
        foreach ($courses as $course) {
            $questionIds = DB::table('reinforcement_questions')
                ->join('reinforcement_question_sets', 'reinforcement_question_sets.id', '=', 'reinforcement_questions.reinforcement_question_set_id')
                ->join('course_module', 'course_module.learning_module_id', '=', 'reinforcement_question_sets.learning_module_id')
                ->where('course_module.course_id', $course->id)
                ->pluck('reinforcement_questions.id')
                ->toArray();

            if (! empty($questionIds)) {
                $questionsByCourse[$course->id] = $questionIds;
            }
        }

        $attemptsCreated = 0;
        $responsesCreated = 0;

        foreach ($completedEnrolments as $enrolment) {
            // ~70% of completed users get a reinforcement attempt
            if (mt_rand(1, 100) > 70) {
                continue;
            }

            // Skip if attempt already exists
            $exists = DB::table('course_reinforcement_attempts')
                ->where('course_id', $enrolment->course_id)
                ->where('user_id', $enrolment->user_id)
                ->exists();

            if ($exists) {
                continue;
            }

            $questionIds = $questionsByCourse[$enrolment->course_id] ?? [];
            if (empty($questionIds)) {
                continue;
            }

            // Determine pass or fail (~60% pass)
            $passed = mt_rand(1, 100) <= 60;
            $score = $passed
                ? mt_rand(70, 100)
                : mt_rand(20, 65);

            $completedAt = \Carbon\Carbon::parse($enrolment->completed_at)->addDays(mt_rand(1, 7));
            $sentAt = $completedAt->copy()->subDays(mt_rand(1, 3));

            $attempt = CourseReinforcementAttempt::create([
                'course_id' => $enrolment->course_id,
                'user_id' => $enrolment->user_id,
                'token' => Str::random(32),
                'status' => $passed ? 'completed' : 'gaps_found',
                'sent_at' => $sentAt,
                'started_at' => $completedAt->copy()->subMinutes(mt_rand(5, 30)),
                'completed_at' => $completedAt,
                'score_percent' => $score,
            ]);
            $attemptsCreated++;

            // Update the course_user reinforcement_status
            DB::table('course_user')
                ->where('course_id', $enrolment->course_id)
                ->where('user_id', $enrolment->user_id)
                ->update([
                    'reinforcement_sent_at' => $sentAt,
                    'reinforcement_status' => $passed ? 'completed' : 'gaps_found',
                ]);

            // Create responses for each question
            foreach ($questionIds as $qId) {
                $isCorrect = $passed
                    ? mt_rand(1, 100) <= 85  // 85% correct for passers
                    : mt_rand(1, 100) <= 35; // 35% correct for failers

                DB::table('course_reinforcement_responses')->insert([
                    'course_reinforcement_attempt_id' => $attempt->id,
                    'reinforcement_question_id' => $qId,
                    'user_id' => $enrolment->user_id,
                    'selected_answer' => $isCorrect ? 'A' : 'B',
                    'is_correct' => $isCorrect,
                    'answered_at' => $completedAt->copy()->subMinutes(mt_rand(1, 20)),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $responsesCreated++;
            }
        }

        $this->command->info("Created {$attemptsCreated} reinforcement attempts with {$responsesCreated} responses.");
    }
}
