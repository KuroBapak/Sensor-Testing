<?php

namespace Database\Seeders;

use App\Models\Personnel;
use App\Models\Skill;
use App\Models\SpecialtyCluster;
use Illuminate\Database\Seeder;

class PersonnelSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedSpecialtyClusters();
        $this->seedSkills();
        $this->seedPersonnel();
    }

    private function seedSpecialtyClusters(): void
    {
        $clusters = [
            ['name' => 'Machining & CNC Specialists', 'sort_order' => 1],
            ['name' => 'Robotics & Automation Engineers', 'sort_order' => 2],
            ['name' => 'Quality & Metrology Inspectors', 'sort_order' => 3],
            ['name' => 'Welding & Fabrication Experts', 'sort_order' => 4],
        ];

        foreach ($clusters as $cluster) {
            SpecialtyCluster::updateOrCreate(['name' => $cluster['name']], $cluster);
        }
    }

    private function seedSkills(): void
    {
        $skills = [
            ['skill_name' => '5-Axis CNC Milling'],
            ['skill_name' => 'Sub-Micron Turning'],
            ['skill_name' => 'MasterCAM & NX CAM'],
            ['skill_name' => 'PLC & SCADA Programming'],
            ['skill_name' => 'Industrial Robotics (KUKA/ABB)'],
            ['skill_name' => 'Zeiss CMM 3D Scanning'],
            ['skill_name' => 'NDT Level II Ultrasonic'],
            ['skill_name' => 'ASME GTAW/SMAW 6G Welding'],
        ];

        foreach ($skills as $skill) {
            Skill::updateOrCreate(['skill_name' => $skill['skill_name']], $skill);
        }
    }

    private function seedPersonnel(): void
    {
        $clusters = SpecialtyCluster::all()->keyBy('name');
        $skills = Skill::all()->keyBy('skill_name');

        $personnelData = [
            [
                'cluster_name' => 'Machining & CNC Specialists',
                'registration_id' => 'ENG-CNC-001',
                'status' => true,
                'job_role' => 'Lead 5-Axis CNC Machinist',
                'technical_focus' => 'Inconel & Titanium Aerospace Blisks',
                'qualification_rank' => 5,
                'sort_order' => 1,
                'skills' => ['5-Axis CNC Milling', 'MasterCAM & NX CAM'],
            ],
            [
                'cluster_name' => 'Machining & CNC Specialists',
                'registration_id' => 'ENG-CNC-002',
                'status' => true,
                'job_role' => 'Senior Precision Lathe Specialist',
                'technical_focus' => 'High-Tolerance Subsea Valve Shafts',
                'qualification_rank' => 4,
                'sort_order' => 2,
                'skills' => ['Sub-Micron Turning'],
            ],
            [
                'cluster_name' => 'Robotics & Automation Engineers',
                'registration_id' => 'ENG-ROB-001',
                'status' => true,
                'job_role' => 'Principal Automation Architect',
                'technical_focus' => 'Turnkey Robotic Cells & SCADA Integration',
                'qualification_rank' => 5,
                'sort_order' => 3,
                'skills' => ['PLC & SCADA Programming', 'Industrial Robotics (KUKA/ABB)'],
            ],
            [
                'cluster_name' => 'Quality & Metrology Inspectors',
                'registration_id' => 'ENG-QC-001',
                'status' => true,
                'job_role' => 'Chief Metrologist',
                'technical_focus' => 'AS9100 First Article Inspection & GD&T',
                'qualification_rank' => 5,
                'sort_order' => 4,
                'skills' => ['Zeiss CMM 3D Scanning', 'NDT Level II Ultrasonic'],
            ],
            [
                'cluster_name' => 'Welding & Fabrication Experts',
                'registration_id' => 'ENG-WLD-001',
                'status' => true,
                'job_role' => 'Certified International Welding Specialist (IWS)',
                'technical_focus' => 'ASME Sec IX Clad Overlay & Pressure Vessels',
                'qualification_rank' => 4,
                'sort_order' => 5,
                'skills' => ['ASME GTAW/SMAW 6G Welding'],
            ],
        ];

        foreach ($personnelData as $item) {
            $cluster = $clusters[$item['cluster_name']] ?? null;
            if ($cluster) {
                $skillsToAttach = $item['skills'];
                unset($item['cluster_name'], $item['skills']);
                $item['specialty_cluster_id'] = $cluster->id;

                $person = Personnel::updateOrCreate(['registration_id' => $item['registration_id']], $item);

                $skillIds = [];
                foreach ($skillsToAttach as $skillName) {
                    if (isset($skills[$skillName])) {
                        $skillIds[] = $skills[$skillName]->id;
                    }
                }
                $person->skills()->sync($skillIds);
            }
        }
    }
}
