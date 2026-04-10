<?php

namespace Database\Seeders;

use App\Models\AssignmentReminder;
use App\Models\LearningAsset;
use App\Models\LearningEvent;
use App\Models\LearningModule;
use App\Models\ModuleProgress;
use App\Models\ReinforcementTouchpoint;
use App\Models\SavedLearningModule;
use App\Models\User;
use App\Models\UserPreference;
use App\Notifications\AssignmentReminderNotification;
use App\Services\ReinforcementService;
use Illuminate\Database\Seeder;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class PrototypeDemoSeeder extends Seeder
{
    public const DEMO_ADMIN_EMAIL = 'admin@totalelearning.local';

    public const DEMO_LEARNER_NAMES = [
        'Ava Carter',
        'Ben Morris',
        'Chloe Singh',
        'Daniel Reed',
        'Emma Walsh',
        'Farah Khan',
        'George Patel',
        'Holly Chen',
        'Isla Morgan',
        'Jack Turner',
        'Katie Brooks',
        'Lewis Foster',
        'Mia Cooper',
        'Noah Bailey',
        'Olivia Price',
        'Oscar Hughes',
        'Poppy Ward',
        'Riley James',
        'Sophie Bennett',
        'Thomas Perry',
        'Uma Shah',
        'Victor Long',
        'Willow Scott',
        'Xavier Bell',
        'Yasmin Ali',
        'Zachary Cook',
        'Amelia Ross',
        'Blake Murphy',
        'Caitlin Gray',
        'Dylan Fisher',
        'Erin Powell',
        'Freddie Russell',
        'Grace Ellis',
        'Harvey Bennett',
        'Imogen Clark',
        'Jude Phillips',
        'Keira Watson',
        'Logan Kelly',
        'Megan Sanders',
        'Nathan Foster',
        'Orla Roberts',
        'Paige Richardson',
        'Quinn Howard',
        'Ruby Jenkins',
        'Samira Hussain',
        'Theo Brooks',
        'Una Mitchell',
        'Violet Adams',
        'William Green',
        'Zoe Parker',
    ];

    public const TEAM_ROLE_GROUPS = [
        'Senior Leadership Team (SLT)' => [
            'Headteacher / Principal',
            'Deputy Headteacher',
            'Assistant Headteacher',
            'Business Manager',
            'SENCO (Special Educational Needs Coordinator)',
        ],
        'Teaching Staff' => [
            'Classroom Teacher',
            'Subject Lead',
            'Early Career Teacher (ECT)',
            'Cover Teacher / Supply Staff',
        ],
        'Teaching Support Staff' => [
            'Teaching Assistant (TA)',
            'Learning Support Assistant (LSA)',
            'Higher Level Teaching Assistant (HLTA)',
        ],
        'Safeguarding & Pastoral Team' => [
            'Designated Safeguarding Lead (DSL)',
            'Deputy DSL',
            'Head of Year / Pastoral Lead',
            'School Counsellor',
        ],
    ];

    public const DEMO_SCORM_MODULE_TITLE = 'Customer Data Handling Essentials';

    public const DEMO_SECONDARY_SCORM_MODULE_TITLE = 'Workplace Safety Decision Lab';

    public const DEMO_MANUAL_MODULE_TITLE = 'Manager Coaching Conversations';

    public static function demoLearners(): array
    {
        $profiles = [];
        $teamTopics = [
            'Senior Leadership Team (SLT)' => ['leadership', 'compliance'],
            'Teaching Staff' => ['curriculum', 'compliance'],
            'Teaching Support Staff' => ['safety', 'support'],
            'Safeguarding & Pastoral Team' => ['safeguarding', 'wellbeing'],
        ];
        $teamGoals = [
            'Senior Leadership Team (SLT)' => 'Keep strategic training evidence ready for governors and inspections',
            'Teaching Staff' => 'Stay current on mandatory training and classroom practice refreshers',
            'Teaching Support Staff' => 'Complete practical support and safety learning on time',
            'Safeguarding & Pastoral Team' => 'Maintain current safeguarding and pastoral evidence at all times',
        ];
        $difficultyByRole = [
            'Headteacher / Principal' => 'advanced',
            'Deputy Headteacher' => 'advanced',
            'Assistant Headteacher' => 'intermediate',
            'Business Manager' => 'intermediate',
            'SENCO (Special Educational Needs Coordinator)' => 'intermediate',
            'Subject Lead' => 'intermediate',
            'Higher Level Teaching Assistant (HLTA)' => 'intermediate',
            'Designated Safeguarding Lead (DSL)' => 'advanced',
            'Deputy DSL' => 'intermediate',
            'Head of Year / Pastoral Lead' => 'intermediate',
            'School Counsellor' => 'intermediate',
        ];

        $primaryAssignments = [
            ['team' => 'Teaching Staff', 'role' => 'Classroom Teacher'],
            ['team' => 'Teaching Staff', 'role' => 'Subject Lead'],
            ['team' => 'Teaching Staff', 'role' => 'Early Career Teacher (ECT)'],
            ['team' => 'Teaching Support Staff', 'role' => 'Teaching Assistant (TA)'],
            ['team' => 'Senior Leadership Team (SLT)', 'role' => 'Business Manager'],
            ['team' => 'Safeguarding & Pastoral Team', 'role' => 'Head of Year / Pastoral Lead'],
            ['team' => 'Teaching Support Staff', 'role' => 'Learning Support Assistant (LSA)'],
            ['team' => 'Teaching Staff', 'role' => 'Cover Teacher / Supply Staff'],
        ];

        foreach (self::DEMO_LEARNER_NAMES as $index => $name) {
            $assignment = $primaryAssignments[$index] ?? null;

            if ($assignment === null) {
                $team = array_keys(self::TEAM_ROLE_GROUPS)[$index % count(self::TEAM_ROLE_GROUPS)];
                $roles = self::TEAM_ROLE_GROUPS[$team];
                $assignment = [
                    'team' => $team,
                    'role' => $roles[$index % count($roles)],
                ];
            }

            $team = $assignment['team'];
            $role = $assignment['role'];
            $slug = strtolower(str_replace([' ', '/', '(', ')'], ['.', '.', '', ''], $name));
            $slug = preg_replace('/[^a-z0-9.]+/', '.', $slug ?? '') ?: 'demo.user.'.($index + 1);
            $slug = trim($slug, '.');

            $profiles[] = [
                'name' => $name,
                'email' => $slug.'@example.com',
                'role' => $role,
                'team' => $team,
                'topics' => $teamTopics[$team] ?? ['compliance'],
                'goal' => $teamGoals[$team] ?? 'Stay current on assigned learning',
                'difficulty' => $difficultyByRole[$role] ?? 'beginner',
            ];
        }

        return $profiles;
    }

    public function run(): void
    {
        $demoLearners = collect(self::demoLearners());
        $allRoles = $demoLearners->pluck('role')->unique()->values()->all();
        $secondaryTargetRoles = [
            'Classroom Teacher',
            'Subject Lead',
            'Early Career Teacher (ECT)',
            'Cover Teacher / Supply Staff',
            'Teaching Assistant (TA)',
            'Learning Support Assistant (LSA)',
            'Higher Level Teaching Assistant (HLTA)',
            'Designated Safeguarding Lead (DSL)',
            'Deputy DSL',
            'Head of Year / Pastoral Lead',
            'School Counsellor',
        ];
        $manualTargetRoles = [
            'Headteacher / Principal',
            'Deputy Headteacher',
            'Assistant Headteacher',
            'Business Manager',
            'SENCO (Special Educational Needs Coordinator)',
            'Subject Lead',
            'Designated Safeguarding Lead (DSL)',
            'Deputy DSL',
            'Head of Year / Pastoral Lead',
            'School Counsellor',
        ];

        $admin = User::query()->updateOrCreate(
            ['email' => self::DEMO_ADMIN_EMAIL],
            [
                'name' => 'Admin',
                'password' => Hash::make('password'),
                'is_admin' => true,
                'email_verified_at' => now(),
            ],
        );

        $learners = $demoLearners->map(function (array $row) {
            $user = User::query()->updateOrCreate(
                ['email' => $row['email']],
                [
                    'name' => $row['name'],
                    'password' => Hash::make('password'),
                    'is_admin' => false,
                    'email_verified_at' => now(),
                    'last_login_at' => now()->subDays(random_int(0, 35)),
                ],
            );

            UserPreference::query()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'topics' => $row['topics'],
                    'role' => $row['role'],
                    'team' => $row['team'] ?? null,
                    'goal' => $row['goal'],
                    'difficulty' => $row['difficulty'],
                ],
            );

            return $user;
        })->values();

        $ava = $learners[0];
        $ben = $learners[1];
        $chloe = $learners[2];
        $daniel = $learners[3];
        $emma = $learners[4];
        $farah = $learners[5];
        $george = $learners[6];
        $holly = $learners[7];

        $scormModule = LearningModule::query()->updateOrCreate(
            ['title' => self::DEMO_SCORM_MODULE_TITLE],
            [
                'description' => 'Interactive SCORM prototype covering personal data handling, common red flags, and escalation expectations.',
                'topic' => 'compliance',
                'difficulty' => 'beginner',
                'target_roles' => $allRoles,
                'owner_user_id' => $admin->id,
                'review_status' => 'approved',
                'approved_by' => $admin->id,
                'approved_at' => now(),
                'is_required' => true,
                'compliance_area' => 'data-privacy',
                'refresh_interval_days' => 365,
                'status' => 'published',
                'source_type' => 'scorm',
            ],
        );

        $secondaryScormModule = LearningModule::query()->updateOrCreate(
            ['title' => self::DEMO_SECONDARY_SCORM_MODULE_TITLE],
            [
                'description' => 'Scenario-based SCORM prototype for hazard spotting, escalation choices, and safe decision making.',
                'topic' => 'safety',
                'difficulty' => 'beginner',
                'target_roles' => $secondaryTargetRoles,
                'owner_user_id' => $admin->id,
                'review_status' => 'approved',
                'approved_by' => $admin->id,
                'approved_at' => now(),
                'is_required' => true,
                'compliance_area' => 'workplace-safety',
                'refresh_interval_days' => 365,
                'status' => 'published',
                'source_type' => 'scorm',
            ],
        );

        $manualModule = LearningModule::query()->updateOrCreate(
            ['title' => self::DEMO_MANUAL_MODULE_TITLE],
            [
                'description' => 'Scenario-led manual module for coaching, feedback framing, and weekly follow-up routines.',
                'topic' => 'leadership',
                'difficulty' => 'intermediate',
                'target_roles' => $manualTargetRoles,
                'owner_user_id' => $admin->id,
                'review_status' => 'approved',
                'approved_by' => $admin->id,
                'approved_at' => now(),
                'is_required' => true,
                'compliance_area' => 'people-management',
                'refresh_interval_days' => 180,
                'status' => 'published',
                'source_type' => 'manual',
                'content_text' => 'Use this module to demonstrate required, overdue, and in-progress reporting across the prototype dashboards.',
            ],
        );

        $this->resetDemoState($learners, collect([$scormModule, $secondaryScormModule, $manualModule]));

        $this->seedScormPackage(
            $scormModule,
            'customer-data-handling-demo',
            self::DEMO_SCORM_MODULE_TITLE,
            'This seeded SCORM prototype demonstrates personal data handling, risk flags, and escalation choices.',
            'scenario-2',
            92
        );
        $this->seedScormPackage(
            $secondaryScormModule,
            'workplace-safety-decision-lab-demo',
            self::DEMO_SECONDARY_SCORM_MODULE_TITLE,
            'This seeded SCORM prototype demonstrates hazard recognition, response choices, and safe escalation.',
            'hazard-check',
            88
        );

        ModuleProgress::query()->updateOrCreate(
            ['user_id' => $ava->id, 'learning_module_id' => $scormModule->id],
            [
                'status' => 'completed',
                'percent_complete' => 100,
                'last_position' => ['scorm' => ['completion_status' => 'completed', 'lesson_location' => 'summary']],
                'last_activity_at' => now()->subDays(8),
                'started_at' => now()->subDays(10),
                'completed_at' => now()->subDays(8),
            ],
        );

        ModuleProgress::query()->updateOrCreate(
            ['user_id' => $ben->id, 'learning_module_id' => $scormModule->id],
            [
                'status' => 'in_progress',
                'percent_complete' => 55,
                'last_position' => ['scorm' => ['progress_measure' => 0.55, 'lesson_location' => 'scenario-2']],
                'last_activity_at' => now()->subHours(6),
                'started_at' => now()->subDays(2),
                'completed_at' => null,
            ],
        );

        ModuleProgress::query()->updateOrCreate(
            ['user_id' => $chloe->id, 'learning_module_id' => $scormModule->id],
            [
                'status' => 'in_progress',
                'percent_complete' => 42,
                'last_position' => ['scorm' => ['progress_measure' => 0.42, 'lesson_location' => 'risk-checkpoint']],
                'last_activity_at' => now()->subHours(20),
                'started_at' => now()->subDays(4),
                'completed_at' => null,
            ],
        );

        ModuleProgress::query()->updateOrCreate(
            ['user_id' => $chloe->id, 'learning_module_id' => $secondaryScormModule->id],
            [
                'status' => 'completed',
                'percent_complete' => 100,
                'last_position' => ['scorm' => ['completion_status' => 'completed', 'lesson_location' => 'final-check']],
                'last_activity_at' => now()->subDays(31),
                'started_at' => now()->subDays(34),
                'completed_at' => now()->subDays(31),
            ],
        );

        ModuleProgress::query()->updateOrCreate(
            ['user_id' => $daniel->id, 'learning_module_id' => $secondaryScormModule->id],
            [
                'status' => 'not_started',
                'percent_complete' => 0,
                'last_position' => ['scorm' => ['lesson_location' => null]],
                'last_activity_at' => null,
                'started_at' => null,
                'completed_at' => null,
            ],
        );

        ModuleProgress::query()->updateOrCreate(
            ['user_id' => $ben->id, 'learning_module_id' => $secondaryScormModule->id],
            [
                'status' => 'in_progress',
                'percent_complete' => 18,
                'last_position' => ['scorm' => ['progress_measure' => 0.18, 'lesson_location' => 'hazard-check']],
                'last_activity_at' => now()->subHours(18),
                'started_at' => now()->subDays(1),
                'completed_at' => null,
            ],
        );

        ModuleProgress::query()->updateOrCreate(
            ['user_id' => $emma->id, 'learning_module_id' => $manualModule->id],
            [
                'status' => 'completed',
                'percent_complete' => 100,
                'last_position' => ['manual' => ['section' => 'weekly-check-ins']],
                'last_activity_at' => now()->subDays(12),
                'started_at' => now()->subDays(16),
                'completed_at' => now()->subDays(12),
            ],
        );

        ModuleProgress::query()->updateOrCreate(
            ['user_id' => $farah->id, 'learning_module_id' => $scormModule->id],
            [
                'status' => 'completed',
                'percent_complete' => 100,
                'last_position' => ['scorm' => ['completion_status' => 'completed', 'lesson_location' => 'summary']],
                'last_activity_at' => now()->subDays(5),
                'started_at' => now()->subDays(7),
                'completed_at' => now()->subDays(5),
            ],
        );

        ModuleProgress::query()->updateOrCreate(
            ['user_id' => $farah->id, 'learning_module_id' => $secondaryScormModule->id],
            [
                'status' => 'not_started',
                'percent_complete' => 0,
                'last_position' => ['scorm' => ['lesson_location' => null]],
                'last_activity_at' => null,
                'started_at' => null,
                'completed_at' => null,
            ],
        );

        ModuleProgress::query()->updateOrCreate(
            ['user_id' => $george->id, 'learning_module_id' => $secondaryScormModule->id],
            [
                'status' => 'in_progress',
                'percent_complete' => 64,
                'last_position' => ['scorm' => ['progress_measure' => 0.64, 'lesson_location' => 'response-choices']],
                'last_activity_at' => now()->subHours(14),
                'started_at' => now()->subDays(3),
                'completed_at' => null,
            ],
        );

        ModuleProgress::query()->updateOrCreate(
            ['user_id' => $holly->id, 'learning_module_id' => $scormModule->id],
            [
                'status' => 'not_started',
                'percent_complete' => 0,
                'last_position' => ['scorm' => ['lesson_location' => null]],
                'last_activity_at' => null,
                'started_at' => null,
                'completed_at' => null,
            ],
        );

        $additionalLearners = $learners->slice(8)->values();
        $additionalProfiles = $demoLearners->slice(8)->values();

        foreach ($additionalLearners as $offset => $learner) {
            $profile = $additionalProfiles[$offset];
            $role = $profile['role'];

            $scormState = match ($offset % 5) {
                0 => ['status' => 'completed', 'percent' => 100, 'location' => 'summary', 'score' => 95, 'when' => now()->subDays(4 + $offset)],
                1 => ['status' => 'in_progress', 'percent' => 62, 'location' => 'scenario-2', 'score' => 82, 'when' => now()->subHours(4 + $offset)],
                2 => ['status' => 'in_progress', 'percent' => 28, 'location' => 'checkpoint-a', 'score' => 74, 'when' => now()->subHours(10 + $offset)],
                3 => ['status' => 'not_started', 'percent' => 0, 'location' => null, 'score' => null, 'when' => null],
                default => ['status' => 'completed', 'percent' => 100, 'location' => 'summary', 'score' => 89, 'when' => now()->subDays(9 + $offset)],
            };

            ModuleProgress::query()->updateOrCreate(
                ['user_id' => $learner->id, 'learning_module_id' => $scormModule->id],
                [
                    'status' => $scormState['status'],
                    'percent_complete' => $scormState['percent'],
                    'last_position' => ['scorm' => ['progress_measure' => $scormState['percent'] / 100, 'lesson_location' => $scormState['location']]],
                    'last_activity_at' => $scormState['when'],
                    'started_at' => $scormState['status'] === 'not_started' ? null : now()->subDays(6 + $offset),
                    'completed_at' => $scormState['status'] === 'completed' ? $scormState['when'] : null,
                ],
            );

            if ($scormState['status'] !== 'not_started') {
                $this->seedScormAttempt(
                    $learner->id,
                    $scormModule->id,
                    'index.html',
                    [
                        'status' => $scormState['status'],
                        'lesson_status' => $scormState['status'],
                        'score_raw' => $scormState['score'],
                        'session_time' => $scormState['status'] === 'completed' ? '00:11:10' : '00:06:40',
                        'session_seconds' => $scormState['status'] === 'completed' ? 670 : 400,
                        'percent_complete' => $scormState['percent'],
                        'lesson_location' => $scormState['location'],
                    ],
                    $scormState['when'] ?? now()->subHours(1)
                );
            }

            if (in_array($role, $secondaryTargetRoles, true)) {
                $secondaryState = match (($offset + 1) % 4) {
                    0 => ['status' => 'completed', 'percent' => 100, 'location' => 'final-check', 'score' => 87, 'when' => now()->subDays(2 + $offset)],
                    1 => ['status' => 'in_progress', 'percent' => 48, 'location' => 'hazard-check', 'score' => 76, 'when' => now()->subHours(8 + $offset)],
                    2 => ['status' => 'not_started', 'percent' => 0, 'location' => null, 'score' => null, 'when' => null],
                    default => ['status' => 'in_progress', 'percent' => 19, 'location' => 'response-choices', 'score' => 68, 'when' => now()->subHours(18 + $offset)],
                };

                ModuleProgress::query()->updateOrCreate(
                    ['user_id' => $learner->id, 'learning_module_id' => $secondaryScormModule->id],
                    [
                        'status' => $secondaryState['status'],
                        'percent_complete' => $secondaryState['percent'],
                        'last_position' => ['scorm' => ['progress_measure' => $secondaryState['percent'] / 100, 'lesson_location' => $secondaryState['location']]],
                        'last_activity_at' => $secondaryState['when'],
                        'started_at' => $secondaryState['status'] === 'not_started' ? null : now()->subDays(3 + $offset),
                        'completed_at' => $secondaryState['status'] === 'completed' ? $secondaryState['when'] : null,
                    ],
                );

                if ($secondaryState['status'] !== 'not_started') {
                    $this->seedScormAttempt(
                        $learner->id,
                        $secondaryScormModule->id,
                        'index.html',
                        [
                            'status' => $secondaryState['status'],
                            'lesson_status' => $secondaryState['status'],
                            'score_raw' => $secondaryState['score'],
                            'session_time' => $secondaryState['status'] === 'completed' ? '00:08:30' : '00:05:20',
                            'session_seconds' => $secondaryState['status'] === 'completed' ? 510 : 320,
                            'percent_complete' => $secondaryState['percent'],
                            'lesson_location' => $secondaryState['location'],
                        ],
                        $secondaryState['when'] ?? now()->subHours(1)
                    );
                }
            }

            if (in_array($role, $manualTargetRoles, true)) {
                $manualState = match (($offset + 2) % 3) {
                    0 => ['status' => 'completed', 'percent' => 100, 'when' => now()->subDays(11 + $offset)],
                    1 => ['status' => 'in_progress', 'percent' => 44, 'when' => now()->subDays(1 + $offset)],
                    default => ['status' => 'not_started', 'percent' => 0, 'when' => null],
                };

                ModuleProgress::query()->updateOrCreate(
                    ['user_id' => $learner->id, 'learning_module_id' => $manualModule->id],
                    [
                        'status' => $manualState['status'],
                        'percent_complete' => $manualState['percent'],
                        'last_position' => ['manual' => ['section' => 'weekly-check-ins']],
                        'last_activity_at' => $manualState['when'],
                        'started_at' => $manualState['status'] === 'not_started' ? null : now()->subDays(12 + $offset),
                        'completed_at' => $manualState['status'] === 'completed' ? $manualState['when'] : null,
                    ],
                );
            }

            $primaryReminder = null;
            if ($scormState['status'] === 'not_started') {
                $primaryReminder = AssignmentReminder::query()->updateOrCreate(
                    ['user_id' => $learner->id, 'learning_module_id' => $scormModule->id, 'reminder_type' => 'not_started_nudge', 'due_on' => now()->addDays(($offset % 4) + 1)->toDateString()],
                    ['status' => 'pending', 'sent_at' => null],
                );
            } elseif ($scormState['status'] === 'in_progress') {
                $primaryReminder = AssignmentReminder::query()->updateOrCreate(
                    ['user_id' => $learner->id, 'learning_module_id' => $scormModule->id, 'reminder_type' => $offset % 2 === 0 ? 'due_soon' : 'inactive_nudge', 'due_on' => now()->subDays($offset % 3)->toDateString()],
                    ['status' => $offset % 2 === 0 ? 'pending' : 'sent', 'sent_at' => $offset % 2 === 0 ? null : now()->subHours(6 + $offset)],
                );
            }

            if ($primaryReminder) {
                $this->seedReminderNotification($learner, $primaryReminder, now()->subHours(2 + $offset), $primaryReminder->status === 'sent');
            }

            if ($offset % 2 === 0) {
                SavedLearningModule::query()->firstOrCreate([
                    'user_id' => $learner->id,
                    'learning_module_id' => $secondaryScormModule->id,
                ], [
                    'created_at' => now()->subHours(12 + $offset),
                ]);

                LearningEvent::query()->updateOrCreate(
                    ['user_id' => $learner->id, 'event_type' => 'module_saved', 'entity_type' => 'learning_module', 'entity_id' => $secondaryScormModule->id],
                    ['metadata' => ['seeded' => true, 'topic' => $secondaryScormModule->topic]],
                );
            }
        }

        $dueSoonReminder = AssignmentReminder::query()->updateOrCreate(
            ['user_id' => $chloe->id, 'learning_module_id' => $scormModule->id, 'reminder_type' => 'due_soon', 'due_on' => now()->toDateString()],
            ['status' => 'pending', 'sent_at' => null],
        );

        $inactiveReminder = AssignmentReminder::query()->updateOrCreate(
            ['user_id' => $daniel->id, 'learning_module_id' => $manualModule->id, 'reminder_type' => 'inactive_nudge', 'due_on' => now()->subDays(2)->toDateString()],
            ['status' => 'sent', 'sent_at' => now()->subDay()],
        );

        $notStartedReminder = AssignmentReminder::query()->updateOrCreate(
            ['user_id' => $daniel->id, 'learning_module_id' => $secondaryScormModule->id, 'reminder_type' => 'not_started_nudge', 'due_on' => now()->toDateString()],
            ['status' => 'pending', 'sent_at' => null],
        );

        $safetyReminder = AssignmentReminder::query()->updateOrCreate(
            ['user_id' => $ben->id, 'learning_module_id' => $secondaryScormModule->id, 'reminder_type' => 'due_soon', 'due_on' => now()->addDay()->toDateString()],
            ['status' => 'pending', 'sent_at' => null],
        );

        $managerReminder = AssignmentReminder::query()->updateOrCreate(
            ['user_id' => $emma->id, 'learning_module_id' => $manualModule->id, 'reminder_type' => 'refresh_due', 'due_on' => now()->addDays(5)->toDateString()],
            ['status' => 'sent', 'sent_at' => now()->subHours(7)],
        );

        $farahReminder = AssignmentReminder::query()->updateOrCreate(
            ['user_id' => $farah->id, 'learning_module_id' => $secondaryScormModule->id, 'reminder_type' => 'not_started_nudge', 'due_on' => now()->addDays(2)->toDateString()],
            ['status' => 'pending', 'sent_at' => null],
        );

        $georgeReminder = AssignmentReminder::query()->updateOrCreate(
            ['user_id' => $george->id, 'learning_module_id' => $secondaryScormModule->id, 'reminder_type' => 'overdue', 'due_on' => now()->subDay()->toDateString()],
            ['status' => 'sent', 'sent_at' => now()->subHours(11)],
        );

        $hollyReminder = AssignmentReminder::query()->updateOrCreate(
            ['user_id' => $holly->id, 'learning_module_id' => $scormModule->id, 'reminder_type' => 'not_started_nudge', 'due_on' => now()->addDays(3)->toDateString()],
            ['status' => 'pending', 'sent_at' => null],
        );

        SavedLearningModule::query()->firstOrCreate([
            'user_id' => $chloe->id,
            'learning_module_id' => $secondaryScormModule->id,
        ], [
            'created_at' => now()->subDays(2),
        ]);

        SavedLearningModule::query()->firstOrCreate([
            'user_id' => $daniel->id,
            'learning_module_id' => $scormModule->id,
        ], [
            'created_at' => now()->subDay(),
        ]);

        SavedLearningModule::query()->firstOrCreate([
            'user_id' => $ben->id,
            'learning_module_id' => $secondaryScormModule->id,
        ], [
            'created_at' => now()->subHours(20),
        ]);

        SavedLearningModule::query()->firstOrCreate([
            'user_id' => $ava->id,
            'learning_module_id' => $scormModule->id,
        ], [
            'created_at' => now()->subDays(4),
        ]);

        SavedLearningModule::query()->firstOrCreate([
            'user_id' => $farah->id,
            'learning_module_id' => $secondaryScormModule->id,
        ], [
            'created_at' => now()->subDays(1),
        ]);

        SavedLearningModule::query()->firstOrCreate([
            'user_id' => $george->id,
            'learning_module_id' => $manualModule->id,
        ], [
            'created_at' => now()->subHours(16),
        ]);

        foreach ($learners as $learner) {
            LearningEvent::query()->updateOrCreate(
                ['user_id' => $learner->id, 'event_type' => 'module_viewed', 'entity_type' => 'learning_module', 'entity_id' => $scormModule->id],
                ['metadata' => ['seeded' => true, 'topic' => $scormModule->topic]],
            );
        }

        $secondaryViewLearners = $learners->filter(function (User $learner) use ($secondaryTargetRoles): bool {
            $role = UserPreference::query()->where('user_id', $learner->id)->value('role');

            return in_array($role, $secondaryTargetRoles, true);
        });

        foreach ($secondaryViewLearners as $learner) {
            LearningEvent::query()->updateOrCreate(
                ['user_id' => $learner->id, 'event_type' => 'module_viewed', 'entity_type' => 'learning_module', 'entity_id' => $secondaryScormModule->id],
                ['metadata' => ['seeded' => true, 'topic' => $secondaryScormModule->topic]],
            );
        }

        $manualViewLearners = $learners->filter(function (User $learner) use ($manualTargetRoles): bool {
            $role = UserPreference::query()->where('user_id', $learner->id)->value('role');

            return in_array($role, $manualTargetRoles, true);
        })->take(18);

        foreach ($manualViewLearners as $learner) {
            LearningEvent::query()->updateOrCreate(
                ['user_id' => $learner->id, 'event_type' => 'module_viewed', 'entity_type' => 'learning_module', 'entity_id' => $manualModule->id],
                ['metadata' => ['seeded' => true, 'topic' => $manualModule->topic]],
            );
        }

        LearningEvent::query()->updateOrCreate(
            ['user_id' => $ben->id, 'event_type' => 'module_saved', 'entity_type' => 'learning_module', 'entity_id' => $secondaryScormModule->id],
            ['metadata' => ['seeded' => true, 'topic' => $secondaryScormModule->topic]],
        );

        LearningEvent::query()->updateOrCreate(
            ['user_id' => $daniel->id, 'event_type' => 'module_saved', 'entity_type' => 'learning_module', 'entity_id' => $scormModule->id],
            ['metadata' => ['seeded' => true, 'topic' => $scormModule->topic]],
        );

        LearningEvent::query()->updateOrCreate(
            ['user_id' => $farah->id, 'event_type' => 'module_saved', 'entity_type' => 'learning_module', 'entity_id' => $secondaryScormModule->id],
            ['metadata' => ['seeded' => true, 'topic' => $secondaryScormModule->topic]],
        );

        LearningEvent::query()->updateOrCreate(
            ['user_id' => $george->id, 'event_type' => 'module_saved', 'entity_type' => 'learning_module', 'entity_id' => $manualModule->id],
            ['metadata' => ['seeded' => true, 'topic' => $manualModule->topic]],
        );

        $this->seedScormAttempt(
            $ava->id,
            $scormModule->id,
            'index.html',
            [
                'status' => 'completed',
                'lesson_status' => 'completed',
                'score_raw' => 97,
                'session_time' => '00:12:00',
                'session_seconds' => 720,
                'percent_complete' => 100,
                'lesson_location' => 'summary',
            ],
            now()->subHours(2)
        );

        $this->seedScormAttempt(
            $ben->id,
            $scormModule->id,
            'index.html',
            [
                'status' => 'in_progress',
                'lesson_status' => 'in_progress',
                'score_raw' => 84,
                'session_time' => '00:08:00',
                'session_seconds' => 480,
                'percent_complete' => 55,
                'lesson_location' => 'scenario-2',
            ],
            now()->subMinutes(10)
        );

        $this->seedScormAttempt(
            $chloe->id,
            $secondaryScormModule->id,
            'index.html',
            [
                'status' => 'completed',
                'lesson_status' => 'completed',
                'score_raw' => 68,
                'session_time' => '00:09:00',
                'session_seconds' => 540,
                'percent_complete' => 100,
                'lesson_location' => 'final-check',
            ],
            now()->subDays(3)
        );

        $this->seedScormAttempt(
            $ben->id,
            $secondaryScormModule->id,
            'index.html',
            [
                'status' => 'in_progress',
                'lesson_status' => 'in_progress',
                'score_raw' => 72,
                'session_time' => '00:05:30',
                'session_seconds' => 330,
                'percent_complete' => 18,
                'lesson_location' => 'hazard-check',
            ],
            now()->subHours(18)
        );

        $this->seedScormAttempt(
            $farah->id,
            $scormModule->id,
            'index.html',
            [
                'status' => 'completed',
                'lesson_status' => 'completed',
                'score_raw' => 93,
                'session_time' => '00:10:45',
                'session_seconds' => 645,
                'percent_complete' => 100,
                'lesson_location' => 'summary',
            ],
            now()->subDays(5)
        );

        $this->seedScormAttempt(
            $george->id,
            $secondaryScormModule->id,
            'index.html',
            [
                'status' => 'in_progress',
                'lesson_status' => 'in_progress',
                'score_raw' => 78,
                'session_time' => '00:06:20',
                'session_seconds' => 380,
                'percent_complete' => 64,
                'lesson_location' => 'response-choices',
            ],
            now()->subHours(14)
        );

        $this->seedReminderNotification($chloe, $dueSoonReminder, now()->subHours(3));
        $this->seedReminderNotification($daniel, $inactiveReminder, now()->subDay(), true);
        $this->seedReminderNotification($daniel, $notStartedReminder, now()->subHours(5));
        $this->seedReminderNotification($ben, $safetyReminder, now()->subHours(9));
        $this->seedReminderNotification($emma, $managerReminder, now()->subHours(7), true);
        $this->seedReminderNotification($farah, $farahReminder, now()->subHours(8));
        $this->seedReminderNotification($george, $georgeReminder, now()->subHours(11));
        $this->seedReminderNotification($holly, $hollyReminder, now()->subHours(6));

        if (\Illuminate\Support\Facades\Schema::hasTable('reinforcement_touchpoints')) {
            $reinforcement = app(ReinforcementService::class);
            $reinforcement->syncForUser($ava);
            $reinforcement->syncForUser($chloe);
            $reinforcement->syncForUser($farah);
            $reinforcement->syncForUser($emma);

            $firstTouchpoint = ReinforcementTouchpoint::query()
                ->where('user_id', $ava->id)
                ->where('learning_module_id', $scormModule->id)
                ->where('interval_days', 7)
                ->first();

            if ($firstTouchpoint && $firstTouchpoint->status !== 'completed') {
                $reinforcement->completeForUser($firstTouchpoint, $ava);
            }

            $secondTouchpoint = ReinforcementTouchpoint::query()
                ->where('user_id', $farah->id)
                ->where('learning_module_id', $scormModule->id)
                ->where('interval_days', 7)
                ->first();

            if ($secondTouchpoint && $secondTouchpoint->status !== 'completed') {
                $reinforcement->completeForUser($secondTouchpoint, $farah);
            }
        }
    }

    private function resetDemoState($learners, $modules): void
    {
        $learnerIds = $learners->pluck('id');
        $moduleIds = $modules->pluck('id');

        AssignmentReminder::query()
            ->whereIn('learning_module_id', $moduleIds)
            ->delete();

        LearningEvent::query()
            ->whereIn('user_id', $learnerIds)
            ->where('entity_type', 'learning_module')
            ->whereIn('entity_id', $moduleIds)
            ->delete();

        ModuleProgress::query()
            ->whereIn('user_id', $learnerIds)
            ->whereIn('learning_module_id', $moduleIds)
            ->delete();

        SavedLearningModule::query()
            ->whereIn('user_id', $learnerIds)
            ->whereIn('learning_module_id', $moduleIds)
            ->delete();

        if (\Illuminate\Support\Facades\Schema::hasTable('reinforcement_touchpoints')) {
            ReinforcementTouchpoint::query()
                ->whereIn('user_id', $learnerIds)
                ->whereIn('learning_module_id', $moduleIds)
                ->delete();
        }

        DatabaseNotification::query()
            ->whereIn('notifiable_id', $learnerIds)
            ->where('type', AssignmentReminderNotification::class)
            ->delete();
    }

    private function seedScormPackage(
        LearningModule $module,
        string $packageSlug,
        string $title,
        string $description,
        string $progressLocation,
        int $completionScore
    ): void
    {
        $extractPath = "learning-assets/{$module->id}/scorm/{$packageSlug}";
        $manifest = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<manifest identifier="demo-scorm" version="1.2" xmlns="http://www.imsproject.org/xsd/imscp_rootv1p1p2">
  <organizations default="ORG1">
    <organization identifier="ORG1">
      <title>{$title}</title>
    </organization>
  </organizations>
  <resources>
    <resource identifier="RES1" type="webcontent" href="index.html">
      <file href="index.html"/>
    </resource>
  </resources>
</manifest>
XML;

        $html = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{$title}</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 2rem; background: #f8fafc; color: #0f172a; }
        .card { background: white; border: 1px solid #cbd5e1; border-radius: 16px; padding: 2rem; max-width: 720px; }
        button { padding: 0.75rem 1rem; margin-right: 0.75rem; border: 0; border-radius: 10px; cursor: pointer; }
        .primary { background: #2563eb; color: white; }
        .success { background: #059669; color: white; }
    </style>
</head>
<body>
    <div class="card">
        <h1>{$title}</h1>
        <p>{$description}</p>
        <p><strong>Try these buttons:</strong></p>
        <button class="primary" onclick="API.LMSSetValue('cmi.core.lesson_location', '{$progressLocation}'); API.LMSSetValue('cmi.progress_measure', '0.5'); API.LMSCommit('');">Save 50% Progress</button>
        <button class="success" onclick="API.LMSSetValue('cmi.core.lesson_status', 'completed'); API.LMSSetValue('cmi.core.score.raw', '{$completionScore}'); API.LMSFinish('');">Complete Module</button>
    </div>
</body>
</html>
HTML;

        Storage::disk('local')->put($extractPath.'/imsmanifest.xml', $manifest);
        Storage::disk('local')->put($extractPath.'/index.html', $html);

        $asset = LearningAsset::query()->updateOrCreate(
            [
                'learning_module_id' => $module->id,
                'asset_type' => 'scorm_package',
                'original_filename' => $packageSlug.'.zip',
            ],
            [
                'storage_disk' => 'local',
                'storage_path' => "learning-assets/{$module->id}/packages/{$packageSlug}.zip",
                'extracted_disk' => 'local',
                'extracted_path' => $extractPath,
                'launch_path' => 'index.html',
                'mime_type' => 'application/zip',
                'size_bytes' => 0,
                'status' => 'processed',
                'manifest' => [
                    'title' => $title,
                    'launch_path' => 'index.html',
                    'resource_identifiers' => ['RES1'],
                ],
                'processing_metadata' => [
                    'seeded' => true,
                    'processed_at' => now()->toIso8601String(),
                ],
                'error_message' => null,
            ],
        );

        $module->forceFill([
            'source_type' => 'scorm',
            'source_uri' => $asset->launch_path,
        ])->save();
    }

    private function seedScormAttempt(int $userId, int $moduleId, string $launchPath, array $runtimeMetadata, \Illuminate\Support\Carbon $occurredAt): void
    {
        $launchEvent = LearningEvent::query()->updateOrCreate(
            ['user_id' => $userId, 'event_type' => 'scorm_launched', 'entity_type' => 'learning_module', 'entity_id' => $moduleId],
            ['metadata' => ['seeded' => true, 'launch_path' => $launchPath]],
        );

        $runtimeEvent = LearningEvent::query()->updateOrCreate(
            ['user_id' => $userId, 'event_type' => 'scorm_runtime_committed', 'entity_type' => 'learning_module', 'entity_id' => $moduleId],
            ['metadata' => array_merge(['seeded' => true], $runtimeMetadata)],
        );

        $launchEvent->forceFill(['created_at' => $occurredAt->copy()->subMinutes(5)])->saveQuietly();
        $runtimeEvent->forceFill(['created_at' => $occurredAt])->saveQuietly();
    }

    private function seedReminderNotification(User $user, AssignmentReminder $reminder, \Illuminate\Support\Carbon $createdAt, bool $markRead = false): void
    {
        $user->notify(new AssignmentReminderNotification($reminder->fresh('module')));

        $notification = $user->notifications()
            ->where('type', AssignmentReminderNotification::class)
            ->latest('id')
            ->get()
            ->first(fn (DatabaseNotification $notification) => (int) ($notification->data['reminder_id'] ?? 0) === (int) $reminder->id);

        if (! $notification) {
            return;
        }

        $notification->forceFill([
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
            'read_at' => $markRead ? $createdAt->copy()->addHours(2) : null,
        ])->saveQuietly();
    }
}
