<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Province;
use App\Models\Municipality;
use App\Models\School;
use App\Models\Employee;
use App\Models\Notice;
use App\Models\Form;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Seed Users
        $admin = User::create([
            'firstname' => 'System',
            'lastname' => 'Administrator',
            'name' => 'Administrator',
            'email' => 'admin@deped.gov.ph',
            'employee_id' => 'ADMIN-01',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        $encoder = User::create([
            'firstname' => 'Regional',
            'lastname' => 'Encoder',
            'name' => 'Regional Encoder',
            'email' => 'encoder@example.com',
            'employee_id' => 'ENCODER-01',
            'password' => Hash::make('password'),
            'role' => 'encoder',
        ]);

        $user = User::create([
            'firstname' => 'John',
            'lastname' => 'Doe',
            'name' => 'John Doe',
            'email' => 'johnedoe@example.com',
            'employee_id' => '2024-00123',
            'password' => Hash::make('password'),
            'role' => 'staff',
        ]);

        // 2. Seed Provinces & Municipalities
        $regionData = [
            [
                'name' => "Davao de Oro",
                'code' => "DDO",
                'region' => "Region 11",
                'municipalities' => ["Compostela", "Laak", "Mabini", "Maco", "Maragusan", "Mawab", "Monkayo", "Montevista", "Nabunturan", "New Bataan", "Pantukan"]
            ],
            [
                'name' => "Davao del Norte",
                'code' => "DDN",
                'region' => "Region 11",
                'municipalities' => ["Asuncion", "Braulio E. Dujali", "Carmen", "Kapalong", "New Corella", "San Isidro", "Santo Tomas", "Talaingod"]
            ],
            [
                'name' => "Davao del Sur",
                'code' => "DDS",
                'region' => "Region 11",
                'municipalities' => ["Bansalan", "Davao City", "Hagonoy", "Kiblawan", "Magsaysay", "Malalag", "Matanao", "Padada", "Santa Cruz", "Sulop"]
            ],
            [
                'name' => "Davao Occidental",
                'code' => "DOC",
                'region' => "Region 11",
                'municipalities' => ["Don Marcelino", "Jose Abad Santos", "Malita", "Santa Maria", "Sarangani"]
            ],
            [
                'name' => "Davao Oriental",
                'code' => "DOR",
                'region' => "Region 11",
                'municipalities' => ["Baganga", "Banaybanay", "Boston", "Caraga", "Cateel", "Governor Generoso", "Lupon", "Manay", "San Isidro", "Tarragona"]
            ]
        ];

        foreach ($regionData as $pData) {
            $province = Province::create([
                'name' => $pData['name'],
                'code' => $pData['code'],
                'region' => $pData['region']
            ]);

            foreach ($pData['municipalities'] as $mName) {
                $municipality = Municipality::create([
                    'province_id' => $province->id,
                    'name' => $mName,
                    'code' => strtoupper(substr($mName, 0, 3))
                ]);

                // Create a couple of schools per municipality
                School::create([
                    'municipality_id' => $municipality->id,
                    'name' => "$mName National High School",
                    'code' => "SCH-" . rand(1000, 9999)
                ]);
                School::create([
                    'municipality_id' => $municipality->id,
                    'name' => "$mName Central Elementary School",
                    'code' => "SCH-" . rand(1000, 9999)
                ]);
            }
        }

        // 3. Seed Sample Employees (at least one school in Davao City, Davao del Sur)
        $davaoCityMun = Municipality::where('name', 'Davao City')->first();
        $sampleSchool = School::where('municipality_id', $davaoCityMun->id)->first();

        Employee::create([
            'employee_id' => '2024-00123',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'school_id' => $sampleSchool->id,
            'role' => 'Teacher',
            'email' => 'johnedoe@example.com',
        ]);

        Employee::create([
            'employee_id' => '2024-00555',
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'school_id' => $sampleSchool->id,
            'role' => 'Principal',
            'email' => 'janesmith@example.com',
        ]);

        // 4. Seed Notices
        Notice::create([
            'subject' => 'System Announcement',
            'content' => 'New IPCRF form templates are now available for download. Please ensure you are using the updated 2026 forms.',
            'priority' => 'High',
            'posted_by' => $admin->id,
            'posted_at' => now(),
            'is_active' => true
        ]);

        Notice::create([
            'subject' => 'Deadline Extension',
            'content' => 'The submission deadline for the 1st Semester IPCRF has been extended until the end of the current month.',
            'priority' => 'Medium',
            'posted_by' => $admin->id,
            'posted_at' => now()->subDays(2),
            'is_active' => true
        ]);

        Notice::create([
            'subject' => 'Welcome Notice',
            'content' => 'Welcome to the DSWD IPCR Management System. For technical issues, please file a support ticket.',
            'priority' => 'Low',
            'posted_by' => $admin->id,
            'posted_at' => now()->subDays(5),
            'is_active' => true
        ]);

        // 5. Seed Forms
        Form::create([
            'name' => 'IPCRF Teacher Template',
            'title' => 'IPCRF Template for Teaching Staff',
            'category' => 'Teacher',
            'description' => 'Official DepEd IPCRF template for Teachers (Master Teacher / Teacher I-III).',
            'file_path' => 'templates/ipcrf_teacher.xlsx',
            'file_name' => 'ipcrf_teacher_template.xlsx',
            'file_type' => 'xlsx',
            'file_size' => 102450,
            'download_count' => 15,
            'uploaded_by' => $admin->id,
            'published_at' => now(),
            'is_active' => true
        ]);
        
        Form::create([
            'name' => 'IPCRF Principal Template',
            'title' => 'IPCRF Template for School Heads',
            'category' => 'Principal',
            'description' => 'Official DepEd IPCRF template for Principals and School heads.',
            'file_path' => 'templates/ipcrf_principal.xlsx',
            'file_name' => 'ipcrf_principal_template.xlsx',
            'file_type' => 'xlsx',
            'file_size' => 104500,
            'download_count' => 8,
            'uploaded_by' => $admin->id,
            'published_at' => now(),
            'is_active' => true
        ]);
    }
}
