<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ClassRoom;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ClassRoomSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get or create a lecturer user
        $lecturer = User::where('role', 'lecturer')
            ->orWhere('role', 'dosen')
            ->first();

        if (!$lecturer) {
            // Create a lecturer if none exists
            $lecturer = User::create([
                'name' => 'Dr. Ahmad Lecturer',
                'email' => 'dosen@example.com',
                'password' => bcrypt('password'),
                'role' => 'lecturer',
            ]);
        }

        // Create sample classes
        $classes = [
            [
                'name' => 'Algoritma & Struktur Data',
                'code' => 'CS101',
                'description' => 'Teknik Informatika',
                'teacher_id' => $lecturer->id,
            ],
            [
                'name' => 'Basis Data',
                'code' => 'CS102',
                'description' => 'Teknik Informatika',
                'teacher_id' => $lecturer->id,
            ],
            [
                'name' => 'Pemrograman Web',
                'code' => 'CS103',
                'description' => 'Teknik Informatika',
                'teacher_id' => $lecturer->id,
            ],
            [
                'name' => 'Sistem Operasi',
                'code' => 'CS104',
                'description' => 'Teknik Informatika',
                'teacher_id' => $lecturer->id,
            ],
            [
                'name' => 'Jaringan Komputer',
                'code' => 'CS105',
                'description' => 'Teknik Informatika',
                'teacher_id' => $lecturer->id,
            ],
        ];

        foreach ($classes as $classData) {
            ClassRoom::updateOrCreate(
                ['code' => $classData['code']],
                $classData
            );
        }

        // Get some students
        $students = User::where('role', 'student')
            ->orWhere('role', 'mahasiswa')
            ->limit(10)
            ->get();

        // If no students, create some
        if ($students->isEmpty()) {
            for ($i = 1; $i <= 10; $i++) {
                $students[] = User::create([
                    'name' => 'Student ' . $i,
                    'email' => 'student' . $i . '@example.com',
                    'password' => bcrypt('password'),
                    'role' => 'student',
                ]);
            }
            $students = collect($students);
        }

        // Assign students to classes
        $allClasses = ClassRoom::where('teacher_id', $lecturer->id)->get();
        foreach ($allClasses as $class) {
            // Attach random students to each class
            $randomStudents = $students->random(min(5, $students->count()));
            foreach ($randomStudents as $student) {
                DB::table('class_students')->updateOrInsert(
                    [
                        'class_room_id' => $class->id,
                        'student_id' => $student->id,
                    ],
                    [
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }

        $this->command->info('ClassRoom seeder completed successfully!');
        $this->command->info('Lecturer: ' . $lecturer->email);
        $this->command->info('Classes created: ' . $allClasses->count());
    }
}
