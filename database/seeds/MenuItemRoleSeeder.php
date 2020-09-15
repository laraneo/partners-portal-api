<?php

use App\Role;
use App\MenuItem;
use App\MenuItemRole;
use Illuminate\Database\Seeder;

class MenuItemRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = [ 
            ['menuItem' => 'inicio', 'role' => 'promotor' ],
            [ 'menuItem' => 'notas', 'role' => 'promotor' ],
            [ 'menuItem' => 'socios', 'role' => 'promotor' ],
            [ 'menuItem' => 'actualizacion-datos', 'role' => 'socio' ],
            [ 'menuItem' => 'facturacion', 'role' => 'socio' ],
            [ 'menuItem' => 'reporte-pagos', 'role' => 'socio' ],
            [ 'menuItem' => 'estado-cuenta', 'role' => 'socio' ],
            [ 'menuItem' => 'facturas-por-pagar', 'role' => 'socio' ],
        ];
        foreach ($data as $key => $value) {
            $admin = Role::where('slug', $value['role'])->first();
            $menuItem = MenuItem::where('slug', $value['menuItem'])->first();
            MenuItemRole::create([
                'role_id' => $admin->id,
                'menu_item_id' => $menuItem->id,
            ]);
        }
    }
}
