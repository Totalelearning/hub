<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SCORM Demo Handout</title>
    <style>
        :root {
            --ink: #1e293b;
            --muted: #64748b;
            --line: rgba(203, 213, 225, 0.9);
            --card: rgba(255, 255, 255, 0.96);
            --shadow: 0 18px 48px rgba(43, 82, 138, 0.12);
        }
        body {
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            color: var(--ink);
            margin: 0;
            background: linear-gradient(180deg, #ecfeff 0%, #eff6ff 40%, #f8fafc 100%);
        }
        .page { max-width: 1120px; margin: 0 auto; padding: 32px 24px 48px; }
        .header {
            display: flex;
            justify-content: space-between;
            gap: 24px;
            align-items: flex-start;
            padding: 24px 26px;
            border: 1px solid rgba(191, 219, 254, 0.9);
            border-radius: 28px;
            background: linear-gradient(135deg, rgba(224, 242, 254, 0.95), rgba(238, 242, 255, 0.95));
            box-shadow: var(--shadow);
        }
        .title { font-size: 34px; font-weight: 700; margin: 0; color: #0f172a; }
        .subtitle { margin: 8px 0 0; color: #475569; max-width: 760px; line-height: 1.5; }
        .print-note { color: #64748b; font-size: 14px; line-height: 1.5; }
        .grid { display: grid; gap: 18px; }
        .grid-4 { grid-template-columns: repeat(4, minmax(0, 1fr)); }
        .grid-2 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .card {
            background: var(--card);
            border: 1px solid var(--line);
            border-radius: 26px;
            padding: 18px;
            box-shadow: var(--shadow);
        }
        .band-card {
            overflow: hidden;
            padding: 0;
        }
        .band-card .band {
            padding: 16px 18px;
            border-bottom: 1px solid var(--line);
            background: linear-gradient(135deg, rgba(225, 239, 255, 0.95), rgba(232, 246, 255, 0.95));
        }
        .band-card .content { padding: 18px; }
        .metric { font-size: 30px; font-weight: 700; margin-top: 8px; color: #0f172a; }
        .label { color: #475569; font-size: 13px; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; }
        .section-title { font-size: 22px; font-weight: 700; margin: 30px 0 14px; color: #0f172a; }
        .mono { font-family: "Courier New", monospace; }
        .list { margin: 0; padding-left: 20px; }
        .list li { margin: 8px 0; }
        .table-wrap {
            background: var(--card);
            border: 1px solid var(--line);
            border-radius: 26px;
            overflow: hidden;
            box-shadow: var(--shadow);
        }
        .table-band {
            padding: 16px 18px;
            border-bottom: 1px solid var(--line);
            background: linear-gradient(135deg, rgba(225, 239, 255, 0.95), rgba(232, 246, 255, 0.95));
        }
        table { width: 100%; border-collapse: collapse; background: #fff; }
        th, td { padding: 12px 14px; border-bottom: 1px solid #e2e8f0; text-align: left; font-size: 14px; }
        th { background: #f8fafc; color: #475569; font-weight: 600; }
        tr:last-child td { border-bottom: 0; }
        .chip {
            display: inline-block;
            border: 1px solid rgba(129, 140, 248, 0.28);
            background: rgba(238, 242, 255, 0.82);
            color: #4338ca;
            border-radius: 9999px;
            padding: 6px 12px;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.06em;
            text-transform: uppercase;
        }
        .primary-link {
            display: inline-block;
            border: 1px solid #818cf8;
            background: #eef2ff;
            color: #4338ca;
            border-radius: 9999px;
            padding: 12px 16px;
            font-size: 14px;
            font-weight: 700;
            text-decoration: none;
        }
        @media print {
            body { background: #fff; }
            .page { max-width: none; padding: 0; }
            a { color: inherit; text-decoration: none; }
            .header, .card, .table-wrap { box-shadow: none; }
        }
        @media (max-width: 900px) {
            .grid-4, .grid-2, .header { grid-template-columns: 1fr; display: grid; }
        }
    </style>
</head>
<body>
    <div class="page">
        <div class="header">
            <div>
                <div style="margin-bottom: 12px;">
                    <span class="chip">
                        Demo Scenario: Client Walkthrough
                    </span>
                </div>
                <h1 class="title">SCORM Demo Handout</h1>
                <p class="subtitle">Client-facing summary of the Totale Learning SCORM prototype, seeded demo accounts, and recommended walkthrough order.</p>
            </div>
            <div class="print-note">
                Generated from the admin SCORM overview.<br>
                Source page: <span class="mono">/app/admin/scorm</span>
            </div>
        </div>

        @if ($primaryDemoModule)
            <div style="margin-top: 18px; margin-bottom: 8px;">
                <a href="{{ route('app.modules.show', ['module' => $primaryDemoModule->id]) }}" class="primary-link">
                    Launch Demo Course
                </a>
                <span style="margin-left: 10px; color: #475569; font-size: 14px;">Primary course: <span class="mono">{{ $primaryDemoModule->title }}</span></span>
            </div>
        @endif

        <div class="section-title">Headline Metrics</div>
        <div class="grid grid-4">
            <div class="card">
                <div class="label">SCORM Modules</div>
                <div class="metric">{{ $summary['modules'] }}</div>
            </div>
            <div class="card">
                <div class="label">Learners</div>
                <div class="metric">{{ $summary['learners'] }}</div>
            </div>
            <div class="card">
                <div class="label">Completion Rate</div>
                <div class="metric">{{ $summary['completion_rate'] }}%</div>
            </div>
            <div class="card">
                <div class="label">Average Session</div>
                <div class="metric">{{ $summary['average_session_label'] }}</div>
            </div>
        </div>

        @if (($recentCompletions ?? collect())->isNotEmpty())
            <div class="section-title">Latest Completion Proof</div>
            <div class="grid grid-2">
                @foreach ($recentCompletions->take(2) as $completion)
                    <div class="card band-card">
                        <div class="band">
                            <div class="label">{{ $completion['learner_name'] }}</div>
                        </div>
                        <div class="content">
                            <div><strong>Module:</strong> {{ $completion['module_title'] }}</div>
                            <div style="margin-top: 6px;"><strong>Completed:</strong> {{ $completion['completed_at']?->format('Y-m-d H:i') ?? 'n/a' }}</div>
                            <div style="margin-top: 6px;"><strong>Progress:</strong> {{ $completion['percent_complete'] }}%</div>
                            <div style="margin-top: 6px;"><strong>Score:</strong> {{ $completion['score_raw'] ?? 'n/a' }}</div>
                            <div style="margin-top: 6px;"><strong>Session:</strong> {{ $completion['session_label'] }}</div>
                            @if ($completion['lesson_location'])
                                <div style="margin-top: 6px;"><strong>Location:</strong> <span class="mono">{{ $completion['lesson_location'] }}</span></div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        @php
            $demoLearners = collect(\Database\Seeders\PrototypeDemoSeeder::demoLearners())->take(6)->values();
            $demoReferenceLearner = $demoLearners->first();
        @endphp

        <div class="section-title">Demo Access</div>
        <div class="grid grid-2">
            <div class="card band-card">
                <div class="band">
                    <div class="label">Admin</div>
                </div>
                <div class="content">
                    <div>Email: <span class="mono">admin@totalelearning.local</span></div>
                    <div style="margin-top: 6px;">Password: <span class="mono">Car.van1</span></div>
                </div>
            </div>
            <div class="card band-card">
                <div class="band">
                    <div class="label">Learners</div>
                </div>
                <div class="content">
                    @foreach ($demoLearners as $demoLearner)
                        <div class="mono">{{ $demoLearner['email'] }}</div>
                    @endforeach
                    <div style="margin-top: 6px;">Password: <span class="mono">password</span></div>
                </div>
            </div>
        </div>

        <div class="section-title">Recommended Demo Flow</div>
        <div class="grid grid-2">
            <div class="card band-card">
                <div class="band">
                    <div class="label">Walkthrough Order</div>
                </div>
                <div class="content">
                    <ol class="list">
                        <li>Start on SCORM Overview to establish tracked launches, attempts, completion, and sessions.</li>
                        <li>Open a module to show package status, runtime summary, and attempt history.</li>
                        <li>Open learner evidence from leaderboard or activity rows to show filtered SCORM event timelines.</li>
                        <li>Finish on compliance and exports to show reporting coverage and handoff artifacts.</li>
                    </ol>
                </div>
            </div>
            <div class="card band-card">
                <div class="band">
                    <div class="label">Key Links</div>
                </div>
                <div class="content">
                    <ul class="list">
                        <li><span class="mono">/app/admin/scorm</span></li>
                        <li><span class="mono">/app/admin/compliance?source_type=scorm</span></li>
                        <li><span class="mono">/app/admin/modules</span></li>
                        <li><span class="mono">/app/admin/users</span></li>
                        <li><span class="mono">/app/feed</span></li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="section-title">Run Demo Walkthrough</div>
        <div class="grid grid-2">
            <div class="card band-card">
                <div class="band">
                    <div class="label">Sequence</div>
                </div>
                <div class="content">
                    <ol class="list">
                        <li><strong>SCORM Hub</strong>: <span class="mono">/app/admin/scorm</span></li>
                        @if ($primaryDemoModule)
                            <li><strong>Learner Course</strong>: <span class="mono">{{ route('app.modules.show', ['module' => $primaryDemoModule->id]) }}</span></li>
                        @endif
                        <li><strong>Module Admin</strong>: <span class="mono">/app/admin/modules</span></li>
                        <li><strong>Compliance</strong>: <span class="mono">/app/admin/compliance?source_type=scorm</span></li>
                        <li><strong>Export Evidence</strong>: <span class="mono">/app/admin/scorm/export</span></li>
                    </ol>
                </div>
            </div>
            <div class="card band-card">
                <div class="band">
                    <div class="label">Walkthrough Notes</div>
                </div>
                <div class="content">
                    <ul class="list">
                        <li>Reset demo data first if you need to restore the seeded baseline.</li>
                        <li>Use the learner course to show launch, progress, score, and session capture.</li>
                        <li>Return to module admin and compliance pages to prove the learner action became reporting evidence.</li>
                        <li>Finish with the SCORM overview CSV export as the handoff artifact.</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="section-title">Demo Prep Checklist</div>
        <div class="grid grid-2">
            <div class="card band-card">
                <div class="band">
                    <div class="label">Checklist</div>
                </div>
                <div class="content">
                    <ol class="list">
                        <li>Reset demo data and confirm the restored counts on the SCORM overview.</li>
                        <li>Choose one learner login and the seeded admin login for the walkthrough.</li>
                        <li>Launch the primary SCORM course and trigger a progress or completion update.</li>
                        <li>Return to module admin, learner evidence, and compliance pages to show the reporting trail.</li>
                        <li>Export the SCORM overview CSV or print this handout as the leave-behind artifact.</li>
                    </ol>
                </div>
            </div>
            <div class="card band-card">
                <div class="band">
                    <div class="label">Prep References</div>
                </div>
                <div class="content">
                    <ul class="list">
                        <li><span class="mono">admin@totalelearning.local</span> / <span class="mono">Car.van1</span></li>
                        @if ($demoReferenceLearner)
                            <li><span class="mono">{{ $demoReferenceLearner['email'] }}</span> / <span class="mono">password</span></li>
                        @endif
                        <li><span class="mono">/app/admin/scorm</span> for reset and analytics</li>
                        <li><span class="mono">/app/admin/compliance?source_type=scorm</span> for reporting evidence</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="section-title">Top SCORM Modules</div>
        <div class="table-wrap">
            <div class="table-band">
                <div class="label">Top SCORM Modules</div>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>Module</th>
                        <th>Area</th>
                        <th>Learners</th>
                        <th>Completed</th>
                        <th>Completion</th>
                        <th>Launches</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($moduleRows->take(5) as $row)
                        <tr>
                            <td>{{ $row['title'] }}</td>
                            <td>{{ $row['compliance_area'] }}</td>
                            <td>{{ $row['learner_count'] }}</td>
                            <td>{{ $row['completed_count'] }}</td>
                            <td>{{ $row['completion_rate'] }}%</td>
                            <td>{{ $row['launch_count'] }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">No SCORM modules available.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="section-title">Top Learners</div>
        <div class="table-wrap">
            <div class="table-band">
                <div class="label">Top Learners</div>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>Learner</th>
                        <th>Attempts</th>
                        <th>Average Score</th>
                        <th>Best Score</th>
                        <th>Average Session</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($learnerLeaderboard->take(5) as $row)
                        <tr>
                            <td>{{ $row['learner_name'] }}</td>
                            <td>{{ $row['attempt_count'] }}</td>
                            <td>{{ $row['average_score'] }}</td>
                            <td>{{ $row['best_score'] }}</td>
                            <td>{{ $row['average_session_label'] }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">No scored SCORM learners recorded yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if (($recentCompletions ?? collect())->isNotEmpty())
            <div class="section-title">Recent SCORM Completions</div>
            <div class="table-wrap">
                <div class="table-band">
                    <div class="label">Completion Evidence</div>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>Completed</th>
                            <th>Learner</th>
                            <th>Module</th>
                            <th>Progress</th>
                            <th>Score</th>
                            <th>Session</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($recentCompletions->take(5) as $completion)
                            <tr>
                                <td>{{ $completion['completed_at']?->format('Y-m-d H:i') ?? 'n/a' }}</td>
                                <td>{{ $completion['learner_name'] }}</td>
                                <td>{{ $completion['module_title'] }}</td>
                                <td>{{ $completion['percent_complete'] }}%</td>
                                <td>{{ $completion['score_raw'] ?? 'n/a' }}</td>
                                <td>{{ $completion['session_label'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</body>
</html>
