<?php

namespace Database\Seeders;

use App\Models\Base;
use App\Models\Company;
use App\Models\Department;
use App\Models\EmployeeClass;
use App\Models\EmploymentStatus;
use App\Models\Position;
use App\Models\Section;
use App\Models\Unit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;

class EmployeeMasterDataSeeder extends Seeder
{
    public function run(): void
    {
        $restore = static function (Model $model): Model {
            if (method_exists($model, 'trashed') && $model->trashed()) {
                $model->restore();
            }

            return $model;
        };

        $company = $restore(Company::withTrashed()->updateOrCreate(
            ['company_code' => 'DEV-BOIE'],
            ['company_name' => 'BOIE Incorporated', 'remarks' => 'Development seed data', 'is_active' => true]
        ));

        $base = $restore(Base::withTrashed()->updateOrCreate(
            ['base_code' => 'DEV-MAIN'],
            ['base_name' => 'Main Office', 'remarks' => 'Development seed data', 'is_active' => true]
        ));

        $unit = $restore(Unit::withTrashed()->updateOrCreate(
            ['unit_code' => 'DEV-CORP'],
            ['base_id' => $base->id, 'unit_name' => 'Corporate Services', 'remarks' => 'Development seed data', 'is_active' => true]
        ));

        $department = $restore(Department::withTrashed()->updateOrCreate(
            ['department_code' => 'DEV-HR'],
            ['unit_id' => $unit->id, 'department_name' => 'Human Resources', 'remarks' => 'Development seed data', 'is_active' => true]
        ));

        $section = $restore(Section::withTrashed()->updateOrCreate(
            ['section_code' => 'DEV-HROPS'],
            ['department_id' => $department->id, 'section_name' => 'HR Operations', 'remarks' => 'Development seed data', 'is_active' => true]
        ));

        $restore(Position::withTrashed()->updateOrCreate(
            ['position_code' => 'DEV-HRSTAFF'],
            ['section_id' => $section->id, 'position_name' => 'HR Staff', 'remarks' => 'Development seed data', 'is_active' => true]
        ));

        foreach ([
            'DEV-PROB' => 'Probationary',
            'DEV-REG' => 'Regular',
            'DEV-CONT' => 'Contractual',
            'DEV-PROJ' => 'Project-Based',
            'DEV-RES' => 'Resigned',
        ] as $code => $name) {
            $restore(EmploymentStatus::withTrashed()->updateOrCreate(
                ['status_code' => $code],
                ['status_name' => $name, 'remarks' => 'Development seed data', 'is_active' => true]
            ));
        }

        foreach ([
            'DEV-RAF' => 'Rank and File',
            'DEV-SUPV' => 'Supervisory',
            'DEV-MGR' => 'Managerial',
        ] as $code => $name) {
            $restore(EmployeeClass::withTrashed()->updateOrCreate(
                ['class_code' => $code],
                ['class_name' => $name, 'remarks' => 'Development seed data', 'is_active' => true]
            ));
        }
    }
}
