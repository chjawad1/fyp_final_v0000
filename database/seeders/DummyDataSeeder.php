<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Project;
use App\Models\SupervisorProfile;
use App\Models\ScopeDocument;
use App\Models\FypPhase;
use App\Models\Committee;
use App\Models\Evaluator;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class DummyDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🚀 Starting FYP Dummy Data Seeder...');

        // Create Admin Users
        $this->createAdmins();

        // Create Supervisors with Profiles
        $supervisors = $this->createSupervisors();

        // Create Students
        $students = $this->createStudents();

        // Create FYP Phases
        $admin = User::where('role', 'admin')->first();
        $this->createFypPhases($admin);

        // Create Projects with different statuses
        $this->createProjects($students, $supervisors);

        // Create Committees (only if table exists)
        if (Schema::hasTable('committees')) {
            $this->createCommittees($admin, $supervisors);
        }

        // Create Evaluators (only if table exists)
        if (Schema::hasTable('evaluators')) {
            $this->createEvaluators($supervisors);
        }

        $this->command->info('✅ Dummy data seeded successfully!');
    }

    /**
     * Create Admin Users
     */
    private function createAdmins(): void
    {
        $this->command->info('Creating Admin Users...');

        $admins = [
            [
                'name' => 'Super Admin',
                'email' => 'admin@fyp.com',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Dr. Muhammad Ali',
                'email' => 'ali.admin@fyp.com',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'email_verified_at' => now(),
            ],
        ];

        foreach ($admins as $admin) {
            User::updateOrCreate(
                ['email' => $admin['email']],
                $admin
            );
        }
    }

    /**
     * Create Supervisors with Profiles
     */
    private function createSupervisors(): array
    {
        $this->command->info('Creating Supervisors...');

        $supervisorsData = [
            [
                'name' => 'Dr. Ahmed Khan',
                'email' => 'ahmed.khan@fyp.com',
                'research_interests' => 'Machine Learning, Deep Learning, Natural Language Processing, Computer Vision',
                'available_slots' => 5,
            ],
            [
                'name' => 'Dr. Fatima Zahra',
                'email' => 'fatima.zahra@fyp.com',
                'research_interests' => 'Web Development, Cloud Computing, Distributed Systems, Microservices',
                'available_slots' => 8,
            ],
            [
                'name' => 'Dr. Hassan Raza',
                'email' => 'hassan.raza@fyp.com',
                'research_interests' => 'Cybersecurity, Network Security, Blockchain, Cryptography',
                'available_slots' => 6,
            ],
            [
                'name' => 'Dr. Ayesha Malik',
                'email' => 'ayesha.malik@fyp.com',
                'research_interests' => 'Data Science, Big Data Analytics, Business Intelligence, Data Visualization',
                'available_slots' => 7,
            ],
            [
                'name' => 'Dr. Usman Ghani',
                'email' => 'usman.ghani@fyp.com',
                'research_interests' => 'Mobile App Development, IoT, Embedded Systems, Robotics',
                'available_slots' => 4,
            ],
            [
                'name' => 'Dr. Sana Ahmed',
                'email' => 'sana.ahmed@fyp.com',
                'research_interests' => 'Software Engineering, Agile Methodologies, DevOps, Quality Assurance',
                'available_slots' => 0,
            ],
        ];

        $supervisors = [];

        foreach ($supervisorsData as $data) {
            $user = User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'password' => Hash::make('password'),
                    'role' => 'supervisor',
                    'email_verified_at' => now(),
                ]
            );

            SupervisorProfile::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'research_interests' => $data['research_interests'],
                    'available_slots' => $data['available_slots'],
                ]
            );

            $supervisors[] = $user;
        }

        return $supervisors;
    }

    /**
     * Create Students
     */
    private function createStudents(): array
    {
        $this->command->info('Creating Students...');

        $studentsData = [
            // Students with NO projects (can create new)
            ['name' => 'Ali Hassan', 'email' => 'ali.hassan@student.fyp.com'],
            ['name' => 'Sara Khan', 'email' => 'sara.khan@student.fyp.com'],
            ['name' => 'Omar Farooq', 'email' => 'omar.farooq@student.fyp.com'],

            // Students who will have PENDING projects
            ['name' => 'Zainab Bibi', 'email' => 'zainab.bibi@student.fyp.com'],
            ['name' => 'Bilal Ahmed', 'email' => 'bilal.ahmed@student.fyp.com'],

            // Students who will have REJECTED projects
            ['name' => 'Hamza Malik', 'email' => 'hamza.malik@student.fyp.com'],
            ['name' => 'Amina Tariq', 'email' => 'amina.tariq@student.fyp.com'],

            // Students who will have APPROVED projects
            ['name' => 'Usman Ali', 'email' => 'usman.ali@student.fyp.com'],
            ['name' => 'Hira Fatima', 'email' => 'hira.fatima@student.fyp.com'],
            ['name' => 'Kashif Iqbal', 'email' => 'kashif.iqbal@student.fyp.com'],

            // Student with 3 pending projects (limit reached)
            ['name' => 'Imran Khan', 'email' => 'imran.khan@student.fyp.com'],

            // Student with 2 pending + 1 rejected
            ['name' => 'Nadia Hussain', 'email' => 'nadia.hussain@student.fyp.com'],

            // Student with completed project
            ['name' => 'Rizwan Shah', 'email' => 'rizwan.shah@student.fyp.com'],
        ];

        $students = [];

        foreach ($studentsData as $data) {
            $user = User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'password' => Hash::make('password'),
                    'role' => 'student',
                    'email_verified_at' => now(),
                ]
            );
            $students[$data['email']] = $user;
        }

        return $students;
    }

    /**
     * Create FYP Phases
     */
    private function createFypPhases($admin): void
    {
        $this->command->info('Creating FYP Phases...');

        $currentDate = Carbon::now();
        $semester = 'Fall 2025';

        $phases = [
            [
                'name' => 'Idea Approval Phase',
                'slug' => 'idea_approval',
                'semester' => $semester,
                'order' => 1,
                'start_date' => $currentDate->copy()->subDays(30),
                'end_date' => $currentDate->copy()->addDays(15),
                'description' => 'Students submit their FYP ideas for supervisor approval.',
                'allow_late' => true,
                'is_active' => true,
                'created_by' => $admin->id,
            ],
            [
                'name' => 'Scope Document Phase',
                'slug' => 'scope_approval',
                'semester' => $semester,
                'order' => 2,
                'start_date' => $currentDate->copy()->addDays(16),
                'end_date' => $currentDate->copy()->addDays(45),
                'description' => 'Students upload detailed scope documents for review.',
                'allow_late' => true,
                'is_active' => true,
                'created_by' => $admin->id,
            ],
            [
                'name' => 'Defence Phase',
                'slug' => 'defence',
                'semester' => $semester,
                'order' => 3,
                'start_date' => $currentDate->copy()->addDays(46),
                'end_date' => $currentDate->copy()->addDays(90),
                'description' => 'Final project defence and evaluation.',
                'allow_late' => false,
                'is_active' => true,
                'created_by' => $admin->id,
            ],
        ];

        foreach ($phases as $phase) {
            FypPhase::updateOrCreate(
                ['slug' => $phase['slug'], 'semester' => $phase['semester']],
                $phase
            );
        }
    }

    /**
     * Create Projects with different statuses
     */
    private function createProjects(array $students, array $supervisors): void
    {
        $this->command->info('Creating Projects...');

        $semester = 'Fall 2025';

        // PENDING PROJECTS
        Project::updateOrCreate(
            ['user_id' => $students['zainab.bibi@student.fyp.com']->id, 'title' => 'AI-Powered Attendance System'],
            [
                'supervisor_id' => $supervisors[0]->id,
                'description' => 'An intelligent attendance management system using facial recognition and machine learning algorithms.',
                'status' => 'pending',
                'current_phase' => 'idea',
                'semester' => $semester,
                'is_late' => false,
            ]
        );

        Project::updateOrCreate(
            ['user_id' => $students['bilal.ahmed@student.fyp.com']->id, 'title' => 'Smart Campus Navigation App'],
            [
                'supervisor_id' => $supervisors[4]->id,
                'description' => 'A mobile application providing indoor navigation for university campus using Bluetooth beacons.',
                'status' => 'pending',
                'current_phase' => 'idea',
                'semester' => $semester,
                'is_late' => false,
            ]
        );

        // REJECTED PROJECTS
        Project::updateOrCreate(
            ['user_id' => $students['hamza.malik@student.fyp.com']->id, 'title' => 'Simple Calculator App'],
            [
                'supervisor_id' => $supervisors[1]->id,
                'description' => 'A basic calculator application for mobile devices.',
                'status' => 'rejected',
                'rejection_reason' => 'The project scope is too simple for an FYP. Please propose a more comprehensive solution.',
                'current_phase' => 'idea',
                'semester' => $semester,
                'is_late' => false,
            ]
        );

        Project::updateOrCreate(
            ['user_id' => $students['amina.tariq@student.fyp.com']->id, 'title' => 'Generic E-Commerce Website'],
            [
                'supervisor_id' => $supervisors[1]->id,
                'description' => 'An online shopping website.',
                'status' => 'rejected',
                'rejection_reason' => 'The proposal lacks innovation. Please add innovative features like AI recommendations.',
                'current_phase' => 'idea',
                'semester' => $semester,
                'is_late' => true,
            ]
        );

        // APPROVED PROJECTS
        $usmanProject = Project::updateOrCreate(
            ['user_id' => $students['usman.ali@student.fyp.com']->id, 'title' => 'Blockchain-Based Voting System'],
            [
                'supervisor_id' => $supervisors[2]->id,
                'description' => 'A secure electronic voting system using blockchain technology.',
                'status' => 'approved',
                'current_phase' => 'scope',
                'semester' => $semester,
                'is_late' => false,
            ]
        );

        // Add scope document
        if (Schema::hasTable('scope_documents') && Schema::hasColumn('scope_documents', 'status')) {
            ScopeDocument::updateOrCreate(
                ['project_id' => $usmanProject->id, 'version' => 'v1'],
                [
                    'user_id' => $students['usman.ali@student.fyp.com']->id,
                    'file_path' => 'scope_documents/dummy_scope_v1.pdf',
                    'changelog' => 'Initial scope document submission',
                    'status' => 'pending',
                ]
            );
        }

        $hiraProject = Project::updateOrCreate(
            ['user_id' => $students['hira.fatima@student.fyp.com']->id, 'title' => 'Healthcare Chatbot with NLP'],
            [
                'supervisor_id' => $supervisors[0]->id,
                'description' => 'An intelligent healthcare assistant chatbot using Natural Language Processing.',
                'status' => 'approved',
                'current_phase' => 'defence',
                'semester' => $semester,
                'is_late' => false,
            ]
        );

        Project::updateOrCreate(
            ['user_id' => $students['kashif.iqbal@student.fyp.com']->id, 'title' => 'Real-time Traffic Monitoring System'],
            [
                'supervisor_id' => $supervisors[3]->id,
                'description' => 'A big data analytics platform for real-time traffic monitoring.',
                'status' => 'approved',
                'current_phase' => 'scope',
                'semester' => $semester,
                'is_late' => false,
            ]
        );

        // STUDENT WITH 3 PENDING PROJECTS (LIMIT REACHED)
        Project::updateOrCreate(
            ['user_id' => $students['imran.khan@student.fyp.com']->id, 'title' => 'Smart Home Automation System'],
            [
                'supervisor_id' => $supervisors[4]->id,
                'description' => 'IoT-based home automation system with voice control.',
                'status' => 'pending',
                'current_phase' => 'idea',
                'semester' => $semester,
                'is_late' => false,
            ]
        );

        Project::updateOrCreate(
            ['user_id' => $students['imran.khan@student.fyp.com']->id, 'title' => 'AI Resume Screener'],
            [
                'supervisor_id' => $supervisors[0]->id,
                'description' => 'Machine learning system to automatically screen job applications.',
                'status' => 'pending',
                'current_phase' => 'idea',
                'semester' => $semester,
                'is_late' => false,
            ]
        );

        Project::updateOrCreate(
            ['user_id' => $students['imran.khan@student.fyp.com']->id, 'title' => 'Decentralized File Storage'],
            [
                'supervisor_id' => $supervisors[2]->id,
                'description' => 'Blockchain-based decentralized file storage system.',
                'status' => 'pending',
                'current_phase' => 'idea',
                'semester' => $semester,
                'is_late' => true,
            ]
        );

        // STUDENT WITH MIXED PROJECTS
        Project::updateOrCreate(
            ['user_id' => $students['nadia.hussain@student.fyp.com']->id, 'title' => 'Online Learning Platform'],
            [
                'supervisor_id' => $supervisors[1]->id,
                'description' => 'E-learning platform with video streaming and quizzes.',
                'status' => 'pending',
                'current_phase' => 'idea',
                'semester' => $semester,
                'is_late' => false,
            ]
        );

        Project::updateOrCreate(
            ['user_id' => $students['nadia.hussain@student.fyp.com']->id, 'title' => 'Fitness Tracking App'],
            [
                'supervisor_id' => $supervisors[4]->id,
                'description' => 'Mobile app for tracking workouts and health metrics.',
                'status' => 'pending',
                'current_phase' => 'idea',
                'semester' => $semester,
                'is_late' => false,
            ]
        );

        Project::updateOrCreate(
            ['user_id' => $students['nadia.hussain@student.fyp.com']->id, 'title' => 'Todo List App'],
            [
                'supervisor_id' => $supervisors[1]->id,
                'description' => 'Simple task management application.',
                'status' => 'rejected',
                'rejection_reason' => 'Too basic for FYP. Consider adding AI-powered task prioritization.',
                'current_phase' => 'idea',
                'semester' => $semester,
                'is_late' => false,
            ]
        );

        // COMPLETED PROJECT
        Project::updateOrCreate(
            ['user_id' => $students['rizwan.shah@student.fyp.com']->id, 'title' => 'Sentiment Analysis Dashboard'],
            [
                'supervisor_id' => $supervisors[3]->id,
                'description' => 'A dashboard for social media sentiment analysis using machine learning.',
                'status' => 'approved',
                'current_phase' => 'completed',
                'semester' => $semester,
                'is_late' => false,
            ]
        );
    }

    /**
     * Create Committees
     */
    private function createCommittees($admin, array $supervisors): void
    {
        $this->command->info('Creating Committees...');

        // Check if committee_members table exists
        if (!Schema::hasTable('committee_members')) {
            $this->command->warn('Skipping committee members - table does not exist.');

            // Just create committees without members
            Committee::updateOrCreate(
                ['name' => 'FYP Evaluation Committee A'],
                [
                    'description' => 'Primary evaluation committee for CS department FYP projects.',
                    'created_by_id' => $admin->id,
                ]
            );

            Committee::updateOrCreate(
                ['name' => 'FYP Evaluation Committee B'],
                [
                    'description' => 'Secondary evaluation committee for overflow projects.',
                    'created_by_id' => $admin->id,
                ]
            );

            return;
        }

        $committees = [
            [
                'name' => 'FYP Evaluation Committee A',
                'description' => 'Primary evaluation committee for CS department FYP projects.',
                'created_by_id' => $admin->id,
                'members' => [
                    ['user_id' => $supervisors[0]->id, 'role' => 'chair'],
                    ['user_id' => $supervisors[1]->id, 'role' => 'member'],
                    ['user_id' => $supervisors[2]->id, 'role' => 'member'],
                ],
            ],
            [
                'name' => 'FYP Evaluation Committee B',
                'description' => 'Secondary evaluation committee for overflow projects.',
                'created_by_id' => $admin->id,
                'members' => [
                    ['user_id' => $supervisors[3]->id, 'role' => 'chair'],
                    ['user_id' => $supervisors[4]->id, 'role' => 'member'],
                ],
            ],
        ];

        foreach ($committees as $data) {
            $committee = Committee::updateOrCreate(
                ['name' => $data['name']],
                [
                    'description' => $data['description'],
                    'created_by_id' => $data['created_by_id'],
                ]
            );

            // Use the relationship to sync members
            $membersToSync = [];
            foreach ($data['members'] as $member) {
                $membersToSync[$member['user_id']] = ['role' => $member['role']];
            }
            $committee->members()->sync($membersToSync);
        }
    }

    /**
     * Create Evaluators
     */
    private function createEvaluators(array $supervisors): void
    {
        $this->command->info('Creating Evaluators...');

        // Check if type column exists
        $hasTypeColumn = Schema::hasColumn('evaluators', 'type');

        foreach ($supervisors as $index => $supervisor) {
            $data = [
                'status' => $index < 3 ? 'assigned' : 'available',
            ];

            if ($hasTypeColumn) {
                $data['type'] = 'supervisor';
            }

            Evaluator::updateOrCreate(
                ['user_id' => $supervisor->id],
                $data
            );
        }
    }
}