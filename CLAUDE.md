# TotaleLearning Hub - LMS

## What this is
A Learning Management System for schools/organisations. Admins assign courses, track compliance, and review knowledge gaps. Learners complete courses and reinforcement knowledge checks.

## Stack
- **Backend**: Laravel 12 / PHP 8.2+ / PostgreSQL 16 with pgvector
- **Frontend**: Bootstrap (AdminUIUX template) + Alpine.js + TailwindCSS
- **Build**: Vite 7, Laravel Sail (Docker)
- **Tests**: PHPUnit 11

## Running locally

```bash
# Start services (from project root)
docker compose up -d          # Laravel (port 80), PostgreSQL (5432), Mailpit (8025)
npm run dev                   # Vite dev server (port 5173)

# Common commands (run inside Docker)
docker compose exec -T laravel.test php artisan <command>
docker compose exec -T laravel.test php artisan tinker --execute="..."
docker compose exec -T laravel.test php artisan db:seed --class=DemoComplianceDataSeeder

# Clear caches after editing blade templates from Windows
docker compose exec -T laravel.test php artisan view:clear
```

## Key architecture decisions

### Course-level, not module-level
The compliance report, analytics dashboard, and admin assignment dashboard all track **course completions** (via `course_user` pivot), not module completions. This was an explicit design decision. Module-level tracking (`ModuleProgress`) is kept only for SCORM-specific features.

### Data model
- `course_user` pivot: `status` (assigned/in_progress/completed), `completed_at`, `reinforcement_sent_at`, `reinforcement_status`
- `UserPreference`: `user_id`, `topics` (JSON), `role`, `team`, `goal`, `difficulty`
- `CourseReinforcementAttempt`: course-level knowledge checks with `score_percent`, `status` (sent/completed/gaps_found)
- `CourseReinforcementResponse`: individual question responses linked to `reinforcement_questions`
- `ReinforcementTouchpoint`: module-level reinforcement (SCORM-specific, separate from course reinforcement)

### Teams and roles
Teams are stored in `user_preferences.team`, not a separate teams table. Current teams:
- Teaching Staff, Senior Leadership Team (SLT), Teaching Support Staff, Safeguarding & Pastoral Team
- IT & Digital Services, Finance & Operations, Student Services, Facilities & Estates, HR & People

Roles are stored in `user_preferences.role` and mapped per team in `PrototypeDemoSeeder::TEAM_ROLE_GROUPS`.

## Admin pages

All admin views use the `layouts.learninguiux` layout with Bootstrap card pattern: `card adminuiux-card shadow-sm`. Card headers use `px-4 py-3 border-bottom` with uppercase eyebrow text.

| Page | Route | Controller |
|------|-------|------------|
| Dashboard | `/app/admin/assignments` | `AdminAssignmentDashboardController` |
| Compliance | `/app/admin/compliance` | `AdminComplianceReportController` |
| Analytics | `/app/admin/course-analytics` | `AdminCourseAnalyticsController` |
| SCORM | `/app/admin/scorm` | `AdminScormOverviewController` |
| Users | `/app/admin/users` | `AdminUserController` |
| Courses | `/app/admin/courses` | `AdminCourseController` |
| Learning Paths | `/app/admin/learning-paths` | `AdminLearningPathController` |

## Auth
- Admin access: `Gate::authorize('admin-access')`
- Demo admin: `admin@totalelearning.local` / `password`
- Demo learners: `firstname.lastname@totalelearning.local` / `password`

## PHP 8.5 gotcha
All `fputcsv()` calls require an explicit 5th escape parameter: `fputcsv($handle, $row, ',', '"', '')`. Without it, PHP 8.5 emits a deprecation warning. Use the helper pattern:
```php
$csv = fn (array $row) => fputcsv($handle, $row, ',', '"', '');
$csv(['Col1', 'Col2', 'Col3']);
```

## CSV exports
Use `response()->streamDownload()` with `fputcsv`. All export routes follow the pattern `{page}/export`.

## Seeders
- `PrototypeDemoSeeder`: Core demo data (50 learners, 4 teams, modules, SCORM data)
- `DemoComplianceDataSeeder`: Extended demo data (36 extra users across 9 teams, varied course completions, 170 reinforcement attempts with mixed pass/fail)
- `ReinforcementQuestionSeeder`: Knowledge check questions per module/course

## Testing
```bash
docker compose exec -T laravel.test php artisan test
docker compose exec -T laravel.test php artisan test --filter=SomeTest
```

Test environment uses array cache, sync queue, array session (configured in phpunit.xml).
