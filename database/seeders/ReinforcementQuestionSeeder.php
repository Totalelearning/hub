<?php

namespace Database\Seeders;

use App\Models\LearningModule;
use App\Models\ReinforcementQuestion;
use App\Models\ReinforcementQuestionSet;
use Illuminate\Database\Seeder;

class ReinforcementQuestionSeeder extends Seeder
{
    public function run(): void
    {
        $questionBank = $this->questionBank();

        foreach ($questionBank as $moduleId => $data) {
            $module = LearningModule::find($moduleId);
            if (! $module) {
                continue;
            }

            // Skip if module already has an approved question set
            $existing = ReinforcementQuestionSet::where('learning_module_id', $moduleId)
                ->where('status', 'approved')
                ->exists();

            if ($existing) {
                $this->command->info("Module #{$moduleId} already has approved questions, skipping.");
                continue;
            }

            // Delete any existing draft/in_review sets for a clean slate
            ReinforcementQuestionSet::where('learning_module_id', $moduleId)
                ->whereIn('status', ['draft', 'in_review'])
                ->delete();

            $set = ReinforcementQuestionSet::create([
                'learning_module_id' => $moduleId,
                'status' => 'approved',
                'generation_mode' => 'manual_seed',
                'title' => $data['title'],
                'summary' => 'Seeded questions for testing the reinforcement system.',
                'generated_at' => now(),
                'reviewed_at' => now(),
            ]);

            foreach ($data['questions'] as $position => $q) {
                ReinforcementQuestion::create([
                    'reinforcement_question_set_id' => $set->id,
                    'position' => $position + 1,
                    'question_text' => $q['question_text'],
                    'question_type' => 'multiple_choice',
                    'options' => $q['options'],
                    'correct_answer' => $q['correct_answer'],
                    'explanation' => $q['explanation'] ?? null,
                    'remediation_learning_module_id' => $moduleId,
                    'status' => 'approved',
                ]);
            }

            $this->command->info("Created {$set->questions()->count()} questions for Module #{$moduleId}: {$module->title}");
        }
    }

    private function questionBank(): array
    {
        return [
            // Module 2: Time Blocking for Busy Students
            2 => [
                'title' => 'Time Blocking Knowledge Check',
                'questions' => [
                    [
                        'question_text' => 'What is the primary purpose of time blocking?',
                        'options' => ['A' => 'To fill every minute of the day with tasks', 'B' => 'To dedicate specific time slots to focused work on particular tasks', 'C' => 'To create a list of tasks in order of importance', 'D' => 'To track how long tasks take to complete'],
                        'correct_answer' => 'B',
                        'explanation' => 'Time blocking is about reserving specific periods for focused, uninterrupted work on predetermined tasks.',
                    ],
                    [
                        'question_text' => 'Which of the following is a common mistake when implementing time blocking?',
                        'options' => ['A' => 'Leaving buffer time between blocks', 'B' => 'Scheduling breaks', 'C' => 'Not accounting for unexpected interruptions', 'D' => 'Using a calendar tool'],
                        'correct_answer' => 'C',
                        'explanation' => 'A rigid schedule without buffer time for interruptions leads to frustration and abandoned time blocks.',
                    ],
                    [
                        'question_text' => 'How should you handle a task that takes longer than its allocated time block?',
                        'options' => ['A' => 'Skip the next block to finish it', 'B' => 'Stop immediately and move on', 'C' => 'Note where you left off and schedule a follow-up block', 'D' => 'Work through break time to complete it'],
                        'correct_answer' => 'C',
                        'explanation' => 'Respecting block boundaries and scheduling follow-ups maintains the discipline of the system.',
                    ],
                ],
            ],

            // Module 3: Memory Techniques That Actually Stick
            3 => [
                'title' => 'Memory Techniques Knowledge Check',
                'questions' => [
                    [
                        'question_text' => 'What is the "spacing effect" in memory science?',
                        'options' => ['A' => 'Learning works better in large open spaces', 'B' => 'Distributing study sessions over time improves retention', 'C' => 'Adding spaces between words aids reading speed', 'D' => 'Taking longer pauses between sentences helps comprehension'],
                        'correct_answer' => 'B',
                        'explanation' => 'The spacing effect shows that spreading practice over time leads to stronger, longer-lasting memories.',
                    ],
                    [
                        'question_text' => 'Which memory technique involves creating vivid mental images linked to a familiar route?',
                        'options' => ['A' => 'Spaced repetition', 'B' => 'The method of loci (memory palace)', 'C' => 'Chunking', 'D' => 'The Feynman technique'],
                        'correct_answer' => 'B',
                        'explanation' => 'The method of loci places items to remember at specific locations along an imagined familiar path.',
                    ],
                    [
                        'question_text' => 'What is "chunking" in the context of memory?',
                        'options' => ['A' => 'Breaking study time into small pieces', 'B' => 'Grouping individual items into larger meaningful units', 'C' => 'Removing unnecessary information', 'D' => 'Repeating information aloud'],
                        'correct_answer' => 'B',
                        'explanation' => 'Chunking groups separate bits of information into meaningful clusters, reducing cognitive load.',
                    ],
                ],
            ],

            // Module 4: Rapid Reading and Note Compression
            4 => [
                'title' => 'Rapid Reading Knowledge Check',
                'questions' => [
                    [
                        'question_text' => 'What is the main goal of note compression?',
                        'options' => ['A' => 'To write down everything the instructor says', 'B' => 'To reduce notes to the essential concepts and relationships', 'C' => 'To use the smallest possible handwriting', 'D' => 'To delete old notes regularly'],
                        'correct_answer' => 'B',
                        'explanation' => 'Note compression distils material to core ideas, making review more efficient and forcing deeper processing.',
                    ],
                    [
                        'question_text' => 'Which reading strategy helps you preview content before deep reading?',
                        'options' => ['A' => 'Subvocalization', 'B' => 'Skimming headings, bold text, and summaries', 'C' => 'Reading every word aloud', 'D' => 'Starting from the last page'],
                        'correct_answer' => 'B',
                        'explanation' => 'Previewing structural elements activates prior knowledge and creates a mental framework for the details.',
                    ],
                    [
                        'question_text' => 'What is subvocalization and how does it affect reading speed?',
                        'options' => ['A' => 'Speaking aloud while reading; it increases speed', 'B' => 'Silently mouthing words while reading; it generally slows reading down', 'C' => 'Highlighting key phrases; it has no effect on speed', 'D' => 'Reading in a quiet voice; it improves comprehension'],
                        'correct_answer' => 'B',
                        'explanation' => 'Subvocalization ties reading speed to speaking speed, which is much slower than visual processing.',
                    ],
                ],
            ],

            // Module 1: Mastering Prompt Basics for Study
            1 => [
                'title' => 'AI Prompting Knowledge Check',
                'questions' => [
                    [
                        'question_text' => 'What makes a prompt "well-structured" when working with AI tools?',
                        'options' => ['A' => 'Using as few words as possible', 'B' => 'Including clear context, a specific task, and desired output format', 'C' => 'Writing in formal academic language only', 'D' => 'Asking yes-or-no questions exclusively'],
                        'correct_answer' => 'B',
                        'explanation' => 'Effective prompts combine context, a clear task description, and guidance on the expected format.',
                    ],
                    [
                        'question_text' => 'Why should you iterate on your prompts rather than accepting the first response?',
                        'options' => ['A' => 'AI always gives wrong answers the first time', 'B' => 'Iterating helps refine the output to better match your specific needs', 'C' => 'It is required by AI usage policies', 'D' => 'The AI needs warm-up questions to function'],
                        'correct_answer' => 'B',
                        'explanation' => 'Iteration refines and focuses the output, helping you get closer to what you actually need.',
                    ],
                    [
                        'question_text' => 'What is a key risk of using AI-generated content without review?',
                        'options' => ['A' => 'The content may be too well-written', 'B' => 'The content could contain inaccuracies or fabricated information', 'C' => 'AI content is always flagged as plagiarism', 'D' => 'There is no risk if the AI is from a reputable provider'],
                        'correct_answer' => 'B',
                        'explanation' => 'AI can generate plausible-sounding but incorrect information, so human review is essential.',
                    ],
                ],
            ],

            // Module 5: Exam-Day Calm and Focus Routine
            5 => [
                'title' => 'Exam-Day Wellbeing Knowledge Check',
                'questions' => [
                    [
                        'question_text' => 'Which breathing technique is commonly recommended for reducing pre-exam anxiety?',
                        'options' => ['A' => 'Rapid shallow breathing to increase alertness', 'B' => 'Slow deep diaphragmatic breathing (e.g., 4-7-8 pattern)', 'C' => 'Holding your breath for as long as possible', 'D' => 'Breathing through the mouth only'],
                        'correct_answer' => 'B',
                        'explanation' => 'Slow, deep breathing activates the parasympathetic nervous system, reducing the stress response.',
                    ],
                    [
                        'question_text' => 'What should you avoid doing the night before an exam?',
                        'options' => ['A' => 'Getting a full night of sleep', 'B' => 'Reviewing summary notes briefly', 'C' => 'Cramming new material for several hours', 'D' => 'Preparing your materials for the next day'],
                        'correct_answer' => 'C',
                        'explanation' => 'Late-night cramming increases anxiety and impairs sleep quality, reducing exam performance.',
                    ],
                    [
                        'question_text' => 'Why is arriving early to an exam beneficial?',
                        'options' => ['A' => 'It gives you time to cram more material', 'B' => 'It allows you to settle in, reduce rush-related stress, and mentally prepare', 'C' => 'It guarantees a better seat', 'D' => 'It is required by most exam policies'],
                        'correct_answer' => 'B',
                        'explanation' => 'Arriving early removes time pressure and provides a buffer for unexpected delays.',
                    ],
                ],
            ],

            // Module 6: Build Better Questions with AI
            6 => [
                'title' => 'AI Question Building Knowledge Check',
                'questions' => [
                    [
                        'question_text' => 'What makes a good question when using AI for learning?',
                        'options' => ['A' => 'Questions that can be answered with a single word', 'B' => 'Open-ended questions that require analysis and explanation', 'C' => 'Questions copied directly from textbooks', 'D' => 'Questions about the AI tool itself'],
                        'correct_answer' => 'B',
                        'explanation' => 'Open-ended, analytical questions push deeper thinking and generate more useful AI responses.',
                    ],
                    [
                        'question_text' => 'How can AI help you identify gaps in your understanding?',
                        'options' => ['A' => 'By telling you what grade you would get', 'B' => 'By generating practice questions that test different aspects of a topic', 'C' => 'By reading your mind', 'D' => 'By comparing you to other students'],
                        'correct_answer' => 'B',
                        'explanation' => 'AI-generated practice questions can target areas you might not have considered studying.',
                    ],
                    [
                        'question_text' => 'What is the Socratic method and how can AI support it?',
                        'options' => ['A' => 'A method of lecturing; AI can generate lectures', 'B' => 'Learning through guided questioning; AI can act as a questioning partner', 'C' => 'A grading system; AI can auto-grade', 'D' => 'A memorisation technique; AI can create flashcards'],
                        'correct_answer' => 'B',
                        'explanation' => 'The Socratic method uses probing questions to deepen understanding, and AI can simulate this dialogue.',
                    ],
                ],
            ],

            // Module 7: Customer Data Handling Essentials
            7 => [
                'title' => 'Data Handling Knowledge Check',
                'questions' => [
                    [
                        'question_text' => 'Under GDPR, what is the principle of "data minimisation"?',
                        'options' => ['A' => 'Collecting as much data as possible for future use', 'B' => 'Only collecting personal data that is necessary for the specific purpose', 'C' => 'Minimising the size of data files', 'D' => 'Deleting all data after 30 days'],
                        'correct_answer' => 'B',
                        'explanation' => 'Data minimisation requires organisations to limit collection to what is directly relevant and necessary.',
                    ],
                    [
                        'question_text' => 'What should you do if you accidentally send customer data to the wrong recipient?',
                        'options' => ['A' => 'Ignore it and hope they do not notice', 'B' => 'Report it immediately as a data breach to your data protection officer', 'C' => 'Ask the recipient to delete it and consider it resolved', 'D' => 'Wait to see if anyone complains'],
                        'correct_answer' => 'B',
                        'explanation' => 'Misdirected personal data is a data breach that must be reported promptly under data protection law.',
                    ],
                    [
                        'question_text' => 'Which of the following is an example of "special category" personal data?',
                        'options' => ['A' => 'Email address', 'B' => 'Job title', 'C' => 'Health information', 'D' => 'Phone number'],
                        'correct_answer' => 'C',
                        'explanation' => 'Health data is a special category under GDPR requiring additional protections and a lawful basis.',
                    ],
                ],
            ],

            // Module 8: Manager Coaching Conversations
            8 => [
                'title' => 'Coaching Conversations Knowledge Check',
                'questions' => [
                    [
                        'question_text' => 'What is the GROW model in coaching?',
                        'options' => ['A' => 'A performance rating scale', 'B' => 'A framework: Goal, Reality, Options, Will', 'C' => 'A career progression pathway', 'D' => 'A team building exercise'],
                        'correct_answer' => 'B',
                        'explanation' => 'GROW structures coaching conversations through Goal setting, Reality assessment, Options exploration, and Will to act.',
                    ],
                    [
                        'question_text' => 'Why are open-ended questions important in coaching conversations?',
                        'options' => ['A' => 'They take longer, which fills the meeting time', 'B' => 'They encourage the coachee to reflect and explore their own thinking', 'C' => 'They are easier for the coach to formulate', 'D' => 'They always lead to the correct answer'],
                        'correct_answer' => 'B',
                        'explanation' => 'Open-ended questions promote self-reflection and ownership, which are central to effective coaching.',
                    ],
                    [
                        'question_text' => 'What should a manager avoid doing during a coaching conversation?',
                        'options' => ['A' => 'Active listening', 'B' => 'Asking clarifying questions', 'C' => 'Immediately providing solutions without exploring the issue', 'D' => 'Summarising what they have heard'],
                        'correct_answer' => 'C',
                        'explanation' => 'Jumping to solutions bypasses the coachee\'s learning process and reduces ownership of the outcome.',
                    ],
                ],
            ],

            // Module 9: Safeguarding test
            9 => [
                'title' => 'Safeguarding Knowledge Check',
                'questions' => [
                    [
                        'question_text' => 'What is your primary duty if you suspect a child or vulnerable adult is at risk of harm?',
                        'options' => ['A' => 'Investigate the situation yourself', 'B' => 'Report your concerns to the designated safeguarding lead immediately', 'C' => 'Wait to see if the situation improves', 'D' => 'Discuss it with colleagues informally'],
                        'correct_answer' => 'B',
                        'explanation' => 'The immediate duty is to report to the designated safeguarding lead; investigation is not your role.',
                    ],
                    [
                        'question_text' => 'Which of the following is a potential indicator of neglect?',
                        'options' => ['A' => 'A child who is consistently well-dressed', 'B' => 'A child who arrives hungry, unwashed, or inappropriately dressed for the weather', 'C' => 'A child who prefers to play alone sometimes', 'D' => 'A child who occasionally forgets homework'],
                        'correct_answer' => 'B',
                        'explanation' => 'Persistent signs of unmet basic needs such as hunger, poor hygiene, or inadequate clothing can indicate neglect.',
                    ],
                    [
                        'question_text' => 'What does the term "professional curiosity" mean in safeguarding?',
                        'options' => ['A' => 'Being nosy about colleagues\' personal lives', 'B' => 'Proactively questioning and not taking things at face value when something feels wrong', 'C' => 'Asking children personal questions about their home life', 'D' => 'Reading research papers about safeguarding trends'],
                        'correct_answer' => 'B',
                        'explanation' => 'Professional curiosity means respectfully probing beneath the surface when indicators raise concern.',
                    ],
                ],
            ],

            // Module 10: Workplace Safety Decision Lab
            10 => [
                'title' => 'Workplace Safety Knowledge Check',
                'questions' => [
                    [
                        'question_text' => 'What should you do first when you discover a hazard in the workplace?',
                        'options' => ['A' => 'Fix it yourself immediately', 'B' => 'Make the area safe if possible and report it to your supervisor', 'C' => 'Ignore it if nobody is nearby', 'D' => 'Send an email to HR and wait for a response'],
                        'correct_answer' => 'B',
                        'explanation' => 'Making the immediate area safe and reporting promptly protects others while the hazard is properly addressed.',
                    ],
                    [
                        'question_text' => 'Under health and safety law, who has responsibility for workplace safety?',
                        'options' => ['A' => 'Only the employer', 'B' => 'Only the health and safety officer', 'C' => 'Both employers and employees share responsibility', 'D' => 'Only the government inspector'],
                        'correct_answer' => 'C',
                        'explanation' => 'Health and safety legislation places duties on both employers (to provide safe conditions) and employees (to follow safe practices).',
                    ],
                    [
                        'question_text' => 'What is a risk assessment?',
                        'options' => ['A' => 'A financial review of insurance costs', 'B' => 'A systematic process of identifying hazards, evaluating risks, and determining control measures', 'C' => 'A list of all accidents that have occurred', 'D' => 'A test employees must pass annually'],
                        'correct_answer' => 'B',
                        'explanation' => 'Risk assessment is a structured process to identify what could cause harm and decide on proportionate controls.',
                    ],
                ],
            ],
        ];
    }
}
