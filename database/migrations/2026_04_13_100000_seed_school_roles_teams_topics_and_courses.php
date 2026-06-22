<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        // ── New roles ───────────────────────────────────────────────
        $existingSlugs = DB::table('roles')->pluck('slug')->all();
        $maxSort = (int) DB::table('roles')->max('sort_order');

        $newRoles = [
            'line_manager' => 'Line Manager',
            'academy_support' => 'Academy Support',
            'inclusion_staff' => 'Inclusion Staff',
            'facilities_staff' => 'Facilities Staff',
            'facilities_assistant_manager' => 'Facilities Assistant Manager',
            'facilities_manager' => 'Facilities Manager',
            'professional_services' => 'Professional Services',
            'estates' => 'Estates',
            'executive' => 'Executive',
            'finance' => 'Finance',
            'ict' => 'ICT',
            'people_hr' => 'People & HR',
        ];

        foreach ($newRoles as $slug => $name) {
            if (! in_array($slug, $existingSlugs, true)) {
                $maxSort++;
                DB::table('roles')->insert([
                    'slug' => $slug,
                    'name' => $name,
                    'sort_order' => $maxSort,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        // ── New teams ───────────────────────────────────────────────
        $existingTeamSlugs = DB::table('teams')->pluck('slug')->all();
        $maxTeamSort = (int) DB::table('teams')->max('sort_order');

        $newTeams = [
            'estates' => 'Estates',
            'executive' => 'Executive',
            'finance' => 'Finance',
            'ict' => 'ICT',
            'people_hr' => 'People & HR',
            'inclusion' => 'Inclusion',
            'facilities' => 'Facilities',
            'professional_services' => 'Professional Services',
        ];

        foreach ($newTeams as $slug => $name) {
            if (! in_array($slug, $existingTeamSlugs, true)) {
                $maxTeamSort++;
                DB::table('teams')->insert([
                    'slug' => $slug,
                    'name' => $name,
                    'sort_order' => $maxTeamSort,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        // ── New topics ──────────────────────────────────────────────
        $existingTopicSlugs = DB::table('topics')->pluck('slug')->all();
        $maxTopicSort = (int) DB::table('topics')->max('sort_order');

        $newTopics = [
            'health-and-safety' => 'Health & Safety',
            'safeguarding' => 'Safeguarding',
            'equality-and-inclusion' => 'Equality & Inclusion',
            'data-protection' => 'Data Protection',
            'first-aid-and-medical' => 'First Aid & Medical',
            'fire-safety' => 'Fire Safety',
            'facilities-and-compliance' => 'Facilities & Compliance',
            'management-and-leadership' => 'Management & Leadership',
            'environmental' => 'Environmental',
        ];

        foreach ($newTopics as $slug => $name) {
            if (! in_array($slug, $existingTopicSlugs, true)) {
                $maxTopicSort++;
                DB::table('topics')->insert([
                    'slug' => $slug,
                    'name' => $name,
                    'sort_order' => $maxTopicSort,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        // ── Courses ─────────────────────────────────────────────────
        $courses = [
            // Health & Safety — All Staff
            ['title' => 'Prevent Duty', 'topic' => 'safeguarding', 'description' => 'Covers the legal responsibility of education staff to support and protect pupils under the Prevent strategy.', 'target_roles' => ['all']],
            ['title' => 'Unconscious Bias', 'topic' => 'equality-and-inclusion', 'description' => 'Awareness of unconscious bias and strategies to reduce it, supporting an inclusive culture.', 'target_roles' => ['all']],
            ['title' => 'Working at Height', 'topic' => 'health-and-safety', 'description' => 'Safety awareness for anyone who steps off the floor, including use of foot steps and ladders.', 'target_roles' => ['all']],
            ['title' => 'Safeguarding Children', 'topic' => 'safeguarding', 'description' => 'Core safeguarding training required for all staff working with children.', 'target_roles' => ['all']],
            ['title' => 'Slips, Trips & Falls', 'topic' => 'health-and-safety', 'description' => 'Prevention of workplace injuries from slips, trips and falls.', 'target_roles' => ['all']],
            ['title' => 'Health & Safety Essentials', 'topic' => 'health-and-safety', 'description' => 'Core health and safety training covering responsibilities and maintaining a safe working environment.', 'target_roles' => ['all']],
            ['title' => 'Display Screen Equipment', 'topic' => 'health-and-safety', 'description' => 'DSE regulations for workers who use display screen equipment daily for continuous periods.', 'target_roles' => ['all']],
            ['title' => 'GDPR UK in Education Training', 'topic' => 'data-protection', 'description' => 'Data protection responsibilities under UK GDPR for education staff.', 'target_roles' => ['all']],
            ['title' => 'Equality, Diversity & Inclusion', 'topic' => 'equality-and-inclusion', 'description' => 'Awareness of EDI significance in the workplace and supporting an inclusive culture.', 'target_roles' => ['all']],
            ['title' => 'Lone Worker Safety', 'topic' => 'health-and-safety', 'description' => 'Safety requirements for staff who work by themselves without close or direct supervision.', 'target_roles' => ['all']],
            ['title' => 'Manual Handling', 'topic' => 'health-and-safety', 'description' => 'Safe techniques for transporting or supporting loads, including lifting, putting down and carrying.', 'target_roles' => ['all']],

            // Academy-based
            ['title' => 'First Aid at Work Refresher', 'topic' => 'first-aid-and-medical', 'description' => 'Refresher training for staff with First Aid responsibilities in their academy.', 'target_roles' => ['all']],
            ['title' => 'Fire Awareness in Education', 'topic' => 'fire-safety', 'description' => 'Knowledge and skills to prevent, respond to and safely evacuate during fire emergencies.', 'target_roles' => ['all']],
            ['title' => 'Asbestos Awareness', 'topic' => 'health-and-safety', 'description' => 'Safety awareness for staff located at sites where asbestos is present.', 'target_roles' => ['all']],

            // Line Managers
            ['title' => 'Health & Safety for Managers & Supervisors', 'topic' => 'management-and-leadership', 'description' => 'Mandatory training for staff with management or supervisory responsibilities.', 'target_roles' => ['Line Manager', 'Headteacher / Principal', 'Deputy Headteacher', 'Assistant Headteacher', 'Business Manager', 'Facilities Manager']],

            // Inclusion / Pastoral / SENCO / DSL / Teaching / Learning Support
            ['title' => 'Young People in the Workplace Training', 'topic' => 'health-and-safety', 'description' => 'Supporting young people experiencing the work environment for the first time through work experience and work-based learning.', 'target_roles' => ['Academy Support', 'Learning Support Assistant (LSA)', 'Classroom Teacher', 'Subject Lead', 'Early Career Teacher (ECT)']],
            ['title' => 'Schools: Children with Asthma', 'topic' => 'first-aid-and-medical', 'description' => 'Training on inhalers, asthma attacks, triggers and emergency response for pupils with asthma.', 'target_roles' => ['Inclusion Staff', 'Pastoral Staff', 'SENCO', 'DSL / Deputy DSL']],
            ['title' => 'Schools: Children with Diabetes', 'topic' => 'first-aid-and-medical', 'description' => 'How to monitor, treat and care for pupils with diabetes in a school setting.', 'target_roles' => ['Inclusion Staff', 'Pastoral Staff', 'SENCO', 'DSL / Deputy DSL']],
            ['title' => 'Schools: Children with Epilepsy', 'topic' => 'first-aid-and-medical', 'description' => 'Understanding different types of epilepsy and appropriate response procedures.', 'target_roles' => ['Inclusion Staff', 'Pastoral Staff', 'SENCO', 'DSL / Deputy DSL']],
            ['title' => 'Schools: Children with Allergies / Anaphylaxis', 'topic' => 'first-aid-and-medical', 'description' => 'Practical guidance on allergy management and anaphylaxis response in schools.', 'target_roles' => ['Inclusion Staff', 'Pastoral Staff', 'SENCO', 'DSL / Deputy DSL']],
            ['title' => 'Autism Awareness', 'topic' => 'equality-and-inclusion', 'description' => 'Understanding the impacts of autism on pupils, adults and families, and current best practices.', 'target_roles' => ['Inclusion Staff', 'Pastoral Staff', 'SENCO', 'DSL / Deputy DSL']],
            ['title' => 'Handling Aggressive Behaviour', 'topic' => 'health-and-safety', 'description' => 'Techniques for managing aggression from parents, pupils or other individuals in the workplace.', 'target_roles' => ['Inclusion Staff', 'Pastoral Staff', 'SENCO', 'DSL / Deputy DSL']],
            ['title' => 'FGM Awareness and Prevention', 'topic' => 'safeguarding', 'description' => 'Recognising signs of potential FGM and intervention procedures for designated safeguarding leads.', 'target_roles' => ['DSL / Deputy DSL']],

            // Facilities
            ['title' => 'COSHH', 'topic' => 'facilities-and-compliance', 'description' => 'Control of Substances Hazardous to Health regulations covering all hazardous substances in the workplace.', 'target_roles' => ['Facilities Staff', 'Facilities Assistant Manager', 'Facilities Manager']],
            ['title' => 'Dust Awareness Training', 'topic' => 'facilities-and-compliance', 'description' => 'COSHH-compliant training on different kinds of dust hazards and protective measures.', 'target_roles' => ['Facilities Staff', 'Facilities Assistant Manager', 'Facilities Manager']],
            ['title' => 'Electrical Safety', 'topic' => 'facilities-and-compliance', 'description' => 'Safe use of electricity and electrical appliances in the workplace.', 'target_roles' => ['Facilities Staff', 'Facilities Assistant Manager', 'Facilities Manager']],
            ['title' => 'Eye Protection', 'topic' => 'facilities-and-compliance', 'description' => 'Identifying hazards and using correct PPE to protect eyes in the workplace.', 'target_roles' => ['Facilities Staff', 'Facilities Assistant Manager', 'Facilities Manager']],
            ['title' => 'Fire Extinguisher Use', 'topic' => 'fire-safety', 'description' => 'Legal requirement for appropriate staff to know how to use fire extinguishers correctly.', 'target_roles' => ['Facilities Staff', 'Facilities Assistant Manager', 'Facilities Manager']],
            ['title' => 'General Workshop Safety', 'topic' => 'facilities-and-compliance', 'description' => 'Best practices for remaining safe in workshop environments.', 'target_roles' => ['Facilities Staff', 'Facilities Assistant Manager', 'Facilities Manager']],
            ['title' => 'Hand Arm Vibration Awareness', 'topic' => 'facilities-and-compliance', 'description' => 'Understanding the cumulative and irreversible effects of vibration exposure.', 'target_roles' => ['Facilities Staff', 'Facilities Assistant Manager', 'Facilities Manager']],
            ['title' => 'Ladder Safety', 'topic' => 'health-and-safety', 'description' => 'Competency training for the safe use of ladders in the workplace.', 'target_roles' => ['Facilities Staff', 'Facilities Assistant Manager', 'Facilities Manager', 'ICT']],
            ['title' => 'Legionella Awareness', 'topic' => 'facilities-and-compliance', 'description' => 'COSHH-regulated training on legionella health risks and prevention in building water systems.', 'target_roles' => ['Facilities Staff', 'Facilities Assistant Manager', 'Facilities Manager']],
            ['title' => 'Managing Contractors', 'topic' => 'management-and-leadership', 'description' => 'Responsibilities when appointing, managing or supervising contractors on site.', 'target_roles' => ['Facilities Staff', 'Facilities Assistant Manager', 'Facilities Manager', 'ICT']],
            ['title' => 'PUWER', 'topic' => 'facilities-and-compliance', 'description' => 'Provision and Use of Work Equipment Regulations 1998 covering safe equipment use.', 'target_roles' => ['Facilities Staff', 'Facilities Assistant Manager', 'Facilities Manager']],
            ['title' => 'Respiratory Protective Equipment (RPE)', 'topic' => 'facilities-and-compliance', 'description' => 'Proper selection and use of respiratory protection against workplace hazards.', 'target_roles' => ['Facilities Staff', 'Facilities Assistant Manager', 'Facilities Manager']],
            ['title' => 'Spill Kits', 'topic' => 'facilities-and-compliance', 'description' => 'COSHH-compliant training on dealing with spills of bodily fluids, chemicals and oils.', 'target_roles' => ['Facilities Staff', 'Facilities Assistant Manager', 'Facilities Manager']],
            ['title' => 'Energy Efficiency Awareness', 'topic' => 'environmental', 'description' => 'Understanding energy efficiency for staff with building management responsibilities.', 'target_roles' => ['Facilities Staff', 'Facilities Assistant Manager', 'Facilities Manager', 'Estates']],
            ['title' => 'Environmental Awareness', 'topic' => 'environmental', 'description' => 'Understanding environmental problems and how workplace practices can address them.', 'target_roles' => ['Facilities Staff', 'Facilities Assistant Manager', 'Facilities Manager', 'Estates']],
            ['title' => 'Project Management Essentials', 'topic' => 'management-and-leadership', 'description' => 'Project management skills suitable for all levels of employees involved in project work.', 'target_roles' => ['Facilities Staff', 'Facilities Manager', 'Estates', 'Professional Services']],
            ['title' => 'The Fire Safety (England) Regulations 2022', 'topic' => 'fire-safety', 'description' => 'Key duties and requirements placed on managers of academy buildings under the 2022 fire safety regulations.', 'target_roles' => ['Facilities Staff', 'Facilities Manager', 'Estates', 'Professional Services']],
            ['title' => 'CDM Regulations', 'topic' => 'facilities-and-compliance', 'description' => 'Construction (Design and Management) Regulations 2015 for construction project management.', 'target_roles' => ['Facilities Assistant Manager', 'Facilities Manager']],

            // Professional Services
            ['title' => 'Health & Safety for Homeworkers', 'topic' => 'health-and-safety', 'description' => 'Safety training for staff who work from home some or all of the time.', 'target_roles' => ['Professional Services']],
            ['title' => 'Driver Awareness', 'topic' => 'health-and-safety', 'description' => 'Legal requirements and responsibilities for staff who drive as part of their role.', 'target_roles' => ['Professional Services']],
        ];

        $existingCourseSlugs = DB::table('courses')->pluck('slug')->all();

        foreach ($courses as $i => $course) {
            $slug = Str::slug($course['title']);
            if (in_array($slug, $existingCourseSlugs, true)) {
                continue;
            }

            DB::table('courses')->insert([
                'title' => $course['title'],
                'slug' => $slug,
                'description' => $course['description'],
                'topic' => $course['topic'],
                'status' => 'published',
                'target_roles' => json_encode($course['target_roles']),
                'sort_order' => $i,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        // Remove courses added by this migration
        $slugs = [
            'prevent-duty', 'unconscious-bias', 'working-at-height', 'safeguarding-children',
            'slips-trips-falls', 'health-safety-essentials', 'display-screen-equipment',
            'gdpr-uk-in-education-training', 'equality-diversity-inclusion', 'lone-worker-safety',
            'manual-handling', 'first-aid-at-work-refresher', 'fire-awareness-in-education',
            'asbestos-awareness', 'health-safety-for-managers-supervisors',
            'young-people-in-the-workplace-training', 'schools-children-with-asthma',
            'schools-children-with-diabetes', 'schools-children-with-epilepsy',
            'schools-children-with-allergies-anaphylaxis', 'autism-awareness',
            'handling-aggressive-behaviour', 'fgm-awareness-and-prevention', 'coshh',
            'dust-awareness-training', 'electrical-safety', 'eye-protection',
            'fire-extinguisher-use', 'general-workshop-safety', 'hand-arm-vibration-awareness',
            'ladder-safety', 'legionella-awareness', 'managing-contractors', 'puwer',
            'respiratory-protective-equipment-rpe', 'spill-kits', 'energy-efficiency-awareness',
            'environmental-awareness', 'project-management-essentials',
            'the-fire-safety-england-regulations-2022', 'cdm-regulations',
            'health-safety-for-homeworkers', 'driver-awareness',
        ];

        DB::table('courses')->whereIn('slug', $slugs)->delete();

        DB::table('topics')->whereIn('slug', [
            'health-and-safety', 'safeguarding', 'equality-and-inclusion', 'data-protection',
            'first-aid-and-medical', 'fire-safety', 'facilities-and-compliance',
            'management-and-leadership', 'environmental',
        ])->delete();

        DB::table('teams')->whereIn('slug', [
            'estates', 'executive', 'finance', 'ict', 'people_hr',
            'inclusion', 'facilities', 'professional_services',
        ])->delete();

        DB::table('roles')->whereIn('slug', [
            'line_manager', 'academy_support', 'inclusion_staff', 'facilities_staff',
            'facilities_assistant_manager', 'facilities_manager', 'professional_services',
            'estates', 'executive', 'finance', 'ict', 'people_hr',
        ])->delete();
    }
};
