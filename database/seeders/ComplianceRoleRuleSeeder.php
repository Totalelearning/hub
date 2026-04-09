<?php

namespace Database\Seeders;

use App\Models\ComplianceRoleRule;
use Illuminate\Database\Seeder;

class ComplianceRoleRuleSeeder extends Seeder
{
    public function run(): void
    {
        $rules = array_merge(
            config('learning_assignments.role_compliance_areas', []),
            [
                'headteacher / principal' => ['data-privacy', 'workplace-safety', 'people-management'],
                'deputy headteacher' => ['data-privacy', 'workplace-safety', 'people-management'],
                'assistant headteacher' => ['data-privacy', 'workplace-safety', 'people-management'],
                'business manager' => ['data-privacy', 'workplace-safety', 'people-management'],
                'senco (special educational needs coordinator)' => ['data-privacy', 'workplace-safety', 'people-management'],
                'classroom teacher' => ['data-privacy', 'workplace-safety'],
                'subject lead' => ['data-privacy', 'workplace-safety', 'people-management'],
                'early career teacher (ect)' => ['data-privacy', 'workplace-safety'],
                'cover teacher / supply staff' => ['data-privacy', 'workplace-safety'],
                'teaching assistant (ta)' => ['data-privacy', 'workplace-safety'],
                'learning support assistant (lsa)' => ['data-privacy', 'workplace-safety'],
                'higher level teaching assistant (hlta)' => ['data-privacy', 'workplace-safety'],
                'designated safeguarding lead (dsl)' => ['data-privacy', 'workplace-safety', 'people-management'],
                'deputy dsl' => ['data-privacy', 'workplace-safety', 'people-management'],
                'head of year / pastoral lead' => ['data-privacy', 'workplace-safety', 'people-management'],
                'school counsellor' => ['data-privacy', 'workplace-safety', 'people-management'],
            ],
        );

        foreach ($rules as $role => $areas) {
            foreach ((array) $areas as $area) {
                ComplianceRoleRule::query()->updateOrCreate(
                    [
                        'role' => strtolower(trim((string) $role)),
                        'compliance_area' => strtolower(trim((string) $area)),
                    ],
                    [],
                );
            }
        }
    }
}
