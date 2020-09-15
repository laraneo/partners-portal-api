<?php

use App\Widget;
use App\Role;
use App\WidgetRole;
use Illuminate\Database\Seeder;

class WidgetTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = [
            [ 
                'name' => 'PARTNERPORTAL_saldo', 
                'slug' => 'PARTNERPORTAL_saldo',
                'description' => 'Widget Saldo',
                'role' => ['administrador','socio']
            ],
            [ 
                'name' => 'PARTNERCONTROL_socios', 
                'slug' => 'PARTNERCONTROL_socios',
                'description' => 'Widget Socios',
                'role' => ['administrador']
            ],
            [ 
                'name' => 'PARTNERCONTROL_familiares', 
                'slug' => 'PARTNERCONTROL_familiares',
                'description' => 'Widget Familiares',
                'role' => ['administrador']
            ],
            [ 
                'name' => 'PARTNERCONTROL_invitados', 
                'slug' => 'PARTNERCONTROL_invitados',
                'description' => 'Widget Invitados',
                'role' => ['administrador']
            ],
        ];
        foreach ($data as $element) {
            $widget = Widget::create([
                'name' => $element['name'],
                'slug' => $element['slug'],
                'description' => $element['description'],
            ]);
            foreach ($element['role'] as $key => $value) {
                $role = Role::where('slug', $value)->first();
                WidgetRole::create([
                    'widget_id' => $widget->id,
                    'role_id' => $role->id,
                ]);
            }
        }
    }
}
