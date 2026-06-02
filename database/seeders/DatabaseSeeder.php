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
        // 0. Seed Positions
        $positions = [
            ["name" => "City/Municipal Links", "department" => "CITY/ MUNICIPAL OPERATIONS OFFICE"],
            ["name" => "City/Municipal Roving Bookkeeper", "department" => "CITY/ MUNICIPAL OPERATIONS OFFICE"],
            ["name" => "Social Welfare Assistant", "department" => "CITY/ MUNICIPAL OPERATIONS OFFICE"],
            ["name" => "Provincial Link", "department" => "PROVINCIAL OPERATIONS OFFICE"],
            ["name" => "Social Welfare Officer III", "department" => "PROVINCIAL OPERATIONS OFFICE"],
            ["name" => "Systems Coordinators", "department" => "PROVINCIAL OPERATIONS OFFICE"],
            ["name" => "Cluster Beneficiary Data Officer", "department" => "PROVINCIAL OPERATIONS OFFICE"],
            ["name" => "Cluster Compliance Verification Officer", "department" => "PROVINCIAL OPERATIONS OFFICE"],
            ["name" => "Provincial Roving Bookkeeper", "department" => "PROVINCIAL OPERATIONS OFFICE"],
            ["name" => "Provincial Monitoring and Evaluation Officer", "department" => "PROVINCIAL OPERATIONS OFFICE"],
            ["name" => "Provincial Grievance Officer", "department" => "PROVINCIAL OPERATIONS OFFICE"],
            ["name" => "Provincial Family Development Session/Capability Building Focal Person", "department" => "PROVINCIAL OPERATIONS OFFICE"],
            ["name" => "Provincial Partnership Officer", "department" => "PROVINCIAL OPERATIONS OFFICE"],
            ["name" => "Administrative Assistant II", "department" => "PROVINCIAL OPERATIONS OFFICE"],
            ["name" => "Admin Aide IV", "department" => "PROVINCIAL OPERATIONS OFFICE"],
            ["name" => "Systems Support Staff", "department" => "PROVINCIAL OPERATIONS OFFICE"],
            ["name" => "Regional Information Technology Officer II", "department" => "REGIONAL PROGRAM MANAGEMENT OFFICE"],
            ["name" => "Regional Information Technology Officer I", "department" => "REGIONAL PROGRAM MANAGEMENT OFFICE"],
            ["name" => "Regional Compliance Verification Officer", "department" => "REGIONAL PROGRAM MANAGEMENT OFFICE"],
            ["name" => "Regional Beneficiary Data Officer", "department" => "REGIONAL PROGRAM MANAGEMENT OFFICE"],
            ["name" => "Cash Grants Focal", "department" => "REGIONAL PROGRAM MANAGEMENT OFFICE"],
            ["name" => "System Support Staff", "department" => "REGIONAL PROGRAM MANAGEMENT OFFICE"],
            ["name" => "Regional Grievance Officer", "department" => "REGIONAL PROGRAM MANAGEMENT OFFICE"],
            ["name" => "Information and Communication Technology Administrator", "department" => "REGIONAL PROGRAM MANAGEMENT OFFICE"],
            ["name" => "Regional Case Manager", "department" => "REGIONAL PROGRAM MANAGEMENT OFFICE"],
            ["name" => "Case Management Technical Officer", "department" => "REGIONAL PROGRAM MANAGEMENT OFFICE"],
            ["name" => "Case Management Technical Staff", "department" => "REGIONAL PROGRAM MANAGEMENT OFFICE"],
            ["name" => "Family Development Session Focal Person", "department" => "REGIONAL PROGRAM MANAGEMENT OFFICE"],
            ["name" => "Family Development Session Technical Officer", "department" => "REGIONAL PROGRAM MANAGEMENT OFFICE"],
            ["name" => "Family Development Session Technical Staff", "department" => "REGIONAL PROGRAM MANAGEMENT OFFICE"],
            ["name" => "Institutional Partnership Development Officer - National Government Agencies", "department" => "REGIONAL PROGRAM MANAGEMENT OFFICE"],
            ["name" => "Institutional Partnership Development Officer - Civil Society Organizations", "department" => "REGIONAL PROGRAM MANAGEMENT OFFICE"],
            ["name" => "Institutional Partnership and Support Services Technical Staff", "department" => "REGIONAL PROGRAM MANAGEMENT OFFICE"],
            ["name" => "MCCT Focal", "department" => "REGIONAL PROGRAM MANAGEMENT OFFICE"],
            ["name" => "Social Safeguards and Intervention Development Technical Officer", "department" => "REGIONAL PROGRAM MANAGEMENT OFFICE"],
            ["name" => "Social Safeguards and Intervention Development Technical Staff", "department" => "REGIONAL PROGRAM MANAGEMENT OFFICE"],
            ["name" => "Indigenous People Focal", "department" => "REGIONAL PROGRAM MANAGEMENT OFFICE"],
            ["name" => "Computer Maintenance Technologist II", "department" => "REGIONAL PROGRAM MANAGEMENT OFFICE"],
            ["name" => "Administrative Aide IV", "department" => "REGIONAL PROGRAM MANAGEMENT OFFICE"],
            ["name" => "Training Specialist II", "department" => "REGIONAL PROGRAM MANAGEMENT OFFICE"],
            ["name" => "Training Specialist I", "department" => "REGIONAL PROGRAM MANAGEMENT OFFICE"],
            ["name" => "Knowledge Management Focal", "department" => "REGIONAL PROGRAM MANAGEMENT OFFICE"],
            ["name" => "Administrative Officer", "department" => "REGIONAL PROGRAM MANAGEMENT OFFICE"],
            ["name" => "Administrative Officer II", "department" => "REGIONAL PROGRAM MANAGEMENT OFFICE"],
            ["name" => "Financial Analyst II", "department" => "REGIONAL PROGRAM MANAGEMENT OFFICE"],
            ["name" => "Administrative Assistant II", "department" => "REGIONAL PROGRAM MANAGEMENT OFFICE"],
            ["name" => "Social Welfare Assistant - Admin", "department" => "REGIONAL PROGRAM MANAGEMENT OFFICE"],
            ["name" => "Administrative Assistant I", "department" => "REGIONAL PROGRAM MANAGEMENT OFFICE"],
            ["name" => "Regional Monitoring and Evaluation Officer", "department" => "REGIONAL PROGRAM MANAGEMENT OFFICE"],
            ["name" => "Monitoring and Evaluation Technical Staff", "department" => "REGIONAL PROGRAM MANAGEMENT OFFICE"]
        ];

        foreach ($positions as $pos) {
            \App\Models\Position::firstOrCreate(
                ['name' => $pos['name']],
                ['description' => "Department/Office: " . $pos['department'], 'is_active' => true]
            );
        }

        // 1. Seed Users
        $superadmin = User::create([
            'firstname' => 'Super',
            'lastname' => 'Admin',
            'name' => 'Super Admin',
            'email' => 'superadmin@deped.gov.ph',
            'employee_id' => 'SUPERADMIN-01',
            'password' => Hash::make('password'),
            'role' => 'superadmin',
            'approved' => true,
        ]);

        $admin = User::create([
            'firstname' => 'System',
            'lastname' => 'Administrator',
            'name' => 'Administrator',
            'email' => 'admin@deped.gov.ph',
            'employee_id' => 'ADMIN-01',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'approved' => true,
        ]);

        $encoder = User::create([
            'firstname' => 'Regional',
            'lastname' => 'Encoder',
            'name' => 'Regional Encoder',
            'email' => 'encoder@example.com',
            'employee_id' => 'ENCODER-01',
            'password' => Hash::make('password'),
            'role' => 'encoder',
            'approved' => true,
        ]);

        $user = User::create([
            'firstname' => 'John',
            'lastname' => 'Doe',
            'name' => 'John Doe',
            'email' => 'johnedoe@example.com',
            'employee_id' => '2024-00123',
            'password' => Hash::make('password'),
            'role' => 'staff',
            'approved' => true,
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
