<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Position;

class PositionSeeder extends Seeder
{
    public function run(): void
    {
        // All positions, deduplicated.
        // "Admin Aide IV" (old seeder) is normalised to "Administrative Aide IV".
        // firstOrCreate ensures this seeder is safe to re-run.
        $positions = [
            // ── Main / General ──────────────────────────────────────────────
            'Social Welfare Officer IV',
            'Social Welfare Officer III',
            'Social Welfare Officer II',
            'Social Welfare Officer I',
            'Social Welfare Assistant',
            'Project Development Officer III',
            'Project Development Officer II',
            'Project Development Officer I',
            'Information Technology Officer II',
            'Computer Maintenance Technologist II',
            'Training Specialist II',
            'Training Specialist I',
            'Administrative Officer IV',
            'Administrative Officer II',
            'Administrative Officer',
            'Administrative Assistant III',
            'Administrative Assistant II',
            'Administrative Assistant I',
            'Administrative Aide IV',
            'Financial Analyst II',

            // ── CITY / MUNICIPAL OPERATIONS OFFICE ──────────────────────────
            'City/Municipal Links',
            'City/Municipal Roving Bookkeeper',

            // ── PROVINCIAL OPERATIONS OFFICE ─────────────────────────────────
            'Provincial Link',
            'Systems Coordinators',
            'Cluster Beneficiary Data Officer',
            'Cluster Compliance Verification Officer',
            'Provincial Roving Bookkeeper',
            'Provincial Monitoring and Evaluation Officer',
            'Provincial Grievance Officer',
            'Provincial Family Development Session/Capability Building Focal Person',
            'Provincial Partnership Officer',
            'Systems Support Staff',

            // ── REGIONAL PROGRAM MANAGEMENT OFFICE ───────────────────────────
            'Regional Information Technology Officer II',
            'Regional Information Technology Officer I',
            'Regional Compliance Verification Officer',
            'Regional Beneficiary Data Officer',
            'Cash Grants Focal',
            'System Support Staff',
            'Regional Grievance Officer',
            'Information and Communication Technology Administrator',
            'Regional Case Manager',
            'Case Management Technical Officer',
            'Case Management Technical Staff',
            'Family Development Session Focal Person',
            'Family Development Session Technical Officer',
            'Family Development Session Technical Staff',
            'Institutional Partnership Development Officer - National Government Agencies',
            'Institutional Partnership Development Officer - Civil Society Organizations',
            'Institutional Partnership and Support Services Technical Staff',
            'MCCT Focal',
            'Social Safeguards and Intervention Development Technical Officer',
            'Social Safeguards and Intervention Development Technical Staff',
            'Indigenous People Focal',
            'Knowledge Management Focal',
            'Social Welfare Assistant - Admin',
            'Regional Monitoring and Evaluation Officer',
            'Monitoring and Evaluation Technical Staff',
        ];

        foreach ($positions as $name) {
            Position::firstOrCreate(
                ['name' => $name],
                ['is_active' => true]
            );
        }

        // Normalise any legacy "Admin Aide IV" records to "Administrative Aide IV"
        Position::where('name', 'Admin Aide IV')->update(['name' => 'Administrative Aide IV']);

        $this->command->info('Positions seeded: ' . count($positions) . ' entries (upserted, duplicates skipped).');
    }
}
