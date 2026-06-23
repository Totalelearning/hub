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

        // Step 4: Create module progress records to match course enrolment statuses
        $this->syncModuleProgress($courses);

        // Step 5: Create reinforcement attempts for completed users
        $this->createReinforcementAttempts($courses);

        // Step 6: Create demo manager users
        $this->createManagerUsers();

        // Step 7: Assign locations to all users who don't have one
        $this->assignLocations();

        $this->command->info('Demo compliance data seeded successfully.');
    }

    private function createManagerUsers(): void
    {
        // Trustee — unrestricted cross-location/team view
        $trustee = User::updateOrCreate(
            ['email' => 'david.wilson@totalelearning.local'],
            [
                'name' => 'David Wilson',
                'password' => Hash::make('password'),
                'system_role' => 'trustee',
            ]
        );
        UserPreference::updateOrCreate(
            ['user_id' => $trustee->id],
            ['team' => 'Senior Leadership Team (SLT)', 'role' => 'Trustee', 'topics' => json_encode(['governance', 'compliance']), 'goal' => 'Oversee whole-school training outcomes', 'difficulty' => 'advanced']
        );

        // SLT Manager — oversees multiple teams
        $sltManager = User::updateOrCreate(
            ['email' => 'slt.manager@totalelearning.local'],
            [
                'name' => 'Sarah Thompson',
                'password' => Hash::make('password'),
                'system_role' => 'slt_manager',
                'managed_teams' => [
                    'Teaching Staff',
                    'Teaching Support Staff',
                    'Senior Leadership Team (SLT)',
                    'Safeguarding & Pastoral Team',
                ],
            ]
        );
        UserPreference::updateOrCreate(
            ['user_id' => $sltManager->id],
            ['team' => 'Senior Leadership Team (SLT)', 'role' => 'Deputy Head', 'topics' => json_encode(['leadership', 'compliance']), 'goal' => 'Oversee staff training compliance', 'difficulty' => 'advanced']
        );

        // Manager — oversees a single team
        $manager = User::updateOrCreate(
            ['email' => 'it.manager@totalelearning.local'],
            [
                'name' => 'James Robertson',
                'password' => Hash::make('password'),
                'system_role' => 'manager',
                'managed_teams' => ['IT & Digital Services'],
            ]
        );
        UserPreference::updateOrCreate(
            ['user_id' => $manager->id],
            ['team' => 'IT & Digital Services', 'role' => 'IT Manager', 'topics' => json_encode(['productivity', 'ai-literacy']), 'goal' => 'Keep IT team skills current', 'difficulty' => 'advanced']
        );

        // Second Manager — oversees HR & People
        $hrManager = User::updateOrCreate(
            ['email' => 'hr.manager@totalelearning.local'],
            [
                'name' => 'Priya Patel',
                'password' => Hash::make('password'),
                'system_role' => 'manager',
                'managed_teams' => ['HR & People'],
            ]
        );
        UserPreference::updateOrCreate(
            ['user_id' => $hrManager->id],
            ['team' => 'HR & People', 'role' => 'HR Manager', 'topics' => json_encode(['compliance', 'wellbeing']), 'goal' => 'Ensure HR team compliance', 'difficulty' => 'intermediate']
        );

        $this->command->info('Created 4 demo role users (Trustee, SLT Manager, IT Manager, HR Manager).');
    }

    private function assignLocations(): void
    {
        $locationIds = DB::table('locations')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->pluck('id')
            ->toArray();

        if (empty($locationIds)) {
            $this->command->warn('No active locations found. Skipping location assignment.');

            return;
        }

        $usersWithoutLocation = DB::table('user_preferences')
            ->whereNull('location_id')
            ->pluck('user_id')
            ->toArray();

        $assigned = 0;

        foreach ($usersWithoutLocation as $i => $userId) {
            $locationId = $locationIds[$i % count($locationIds)];

            DB::table('user_preferences')
                ->where('user_id', $userId)
                ->update(['location_id' => $locationId]);

            $assigned++;
        }

        $this->command->info("Assigned locations to {$assigned} users across " . count($locationIds) . ' locations.');
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

    private function syncModuleProgress($courses): void
    {
        // Build a map of course_id => [module_ids]
        $modulesByCourse = [];
        foreach ($courses as $course) {
            $moduleIds = DB::table('course_module')
                ->where('course_id', $course->id)
                ->orderBy('sort_order')
                ->pluck('learning_module_id')
                ->toArray();

            if (! empty($moduleIds)) {
                $modulesByCourse[$course->id] = $moduleIds;
            }
        }

        // Get all enrolments that have a status beyond "assigned"
        $enrolments = DB::table('course_user')
            ->whereIn('status', ['in_progress', 'completed'])
            ->select('course_id', 'user_id', 'status', 'completed_at', 'updated_at')
            ->get();

        $created = 0;

        foreach ($enrolments as $enrolment) {
            $moduleIds = $modulesByCourse[$enrolment->course_id] ?? [];
            if (empty($moduleIds)) {
                continue;
            }

            foreach ($moduleIds as $index => $moduleId) {
                // Skip if progress already exists
                $exists = DB::table('module_progress')
                    ->where('user_id', $enrolment->user_id)
                    ->where('learning_module_id', $moduleId)
                    ->exists();

                if ($exists) {
                    continue;
                }

                if ($enrolment->status === 'completed') {
                    // All modules completed for completed courses
                    $completedAt = \Carbon\Carbon::parse($enrolment->completed_at)->subMinutes(count($moduleIds) - $index);
                    DB::table('module_progress')->insert([
                        'user_id' => $enrolment->user_id,
                        'learning_module_id' => $moduleId,
                        'status' => 'completed',
                        'percent_complete' => 100,
                        'started_at' => $completedAt->copy()->subMinutes(mt_rand(10, 30)),
                        'completed_at' => $completedAt,
                        'last_activity_at' => $completedAt,
                        'created_at' => $completedAt,
                        'updated_at' => $completedAt,
                    ]);
                } else {
                    // In progress: complete some modules, leave the rest
                    $completeUpTo = mt_rand(0, count($moduleIds) - 1);

                    if ($index < $completeUpTo) {
                        $doneAt = \Carbon\Carbon::parse($enrolment->updated_at)->subDays(mt_rand(1, 7));
                        DB::table('module_progress')->insert([
                            'user_id' => $enrolment->user_id,
                            'learning_module_id' => $moduleId,
                            'status' => 'completed',
                            'percent_complete' => 100,
                            'started_at' => $doneAt->copy()->subMinutes(mt_rand(10, 30)),
                            'completed_at' => $doneAt,
                            'last_activity_at' => $doneAt,
                            'created_at' => $doneAt,
                            'updated_at' => $doneAt,
                        ]);
                    } elseif ($index === $completeUpTo) {
                        // Currently in-progress module
                        $startedAt = \Carbon\Carbon::parse($enrolment->updated_at)->subDays(mt_rand(0, 3));
                        DB::table('module_progress')->insert([
                            'user_id' => $enrolment->user_id,
                            'learning_module_id' => $moduleId,
                            'status' => 'in_progress',
                            'percent_complete' => mt_rand(15, 85),
                            'started_at' => $startedAt,
                            'completed_at' => null,
                            'last_activity_at' => $startedAt,
                            'created_at' => $startedAt,
                            'updated_at' => $startedAt,
                        ]);
                    }
                    // else: not started yet — no record needed
                }
                $created++;
            }
        }

        $this->command->info("Created {$created} module progress records.");
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
