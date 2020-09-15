<?php

use Illuminate\Database\Seeder;

class Menu extends Seeder
{
    /*
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = [
        [ 
            'name' => 'inicio', 
            'slug' => 'inicio', 
            'description' => 'Inicio', 
            'route' => '/dashboard/main', 
            'icon' => '/dashboard/main' 
        ],
        [ 
            'name' => 'notas', 
            'slug' => 'notas', 
            'description' => 'Notas', 
            'route' => '/dashboard/main', 
            'icon' => 'notas' 
        ],
        [ 
            'name' => 'reporte-de-pago', 
            'slug' => 'reporte-de-pago', 
            'description' => 'Reporte de Pago',
            'route' => '/dashboard/reporte-pagos', 
            'icon' => 'reporte-pago' 
        ],
        [ 
            'name' => 'facturas-por-pagar', 
            'slug' => 'facturas-por-pagar', 
            'description' => 
            'Facturas por Pagar',
            'route' => '/dashboard/facturas-por-pagar', 
            'icon' => 'facturas-por-pagar' 
        ],
        ];
        foreach ($data as $element) {
            Menu::create([
                'description' => $element['description'],
                'status' => $element['status'],
            ]);
        }
    }
}
