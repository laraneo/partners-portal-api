<?php

use App\Menu;
use App\MenuItem;
use Illuminate\Database\Seeder;

class MenuTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $menuBase = Menu::create([
            'name' => 'menu-base',
            'slug' => 'menu-base',
            'description' => 'Menu Base',
        ]);

        MenuItem::create([
            'name' => 'Inicio',
            'slug' => 'inicio',
            'parent' => 0,
            'order' => 0,
            'description' => 'Inicio',
            'route' => '/dashboard/main',
            'menu_id' => $menuBase->id,
        ]);

        MenuItem::create([
            'name' => 'Notas',
            'slug' => 'notas',
            'parent' => 0,
            'order' => 0,
            'description' => 'Notas',
            'route' => '/dashboard/main',
            'menu_id' => $menuBase->id,
        ]);

        MenuItem::create([
            'name' => 'Actualizacion de Datos',
            'slug' => 'actualizacion-datos',
            'parent' => 0,
            'order' => 0,
            'description' => 'Actualizacion de Datos',
            'route' => '/dashboard/actualizacion-datos',
            'menu_id' => $menuBase->id,
        ]);

       $fact = MenuItem::create([
            'name' => 'Facturacion',
            'slug' => 'facturacion',
            'parent' => 0,
            'order' => 0,
            'description' => 'Facturacion',
            'route' => null,
            'menu_id' => $menuBase->id,
        ]);

        MenuItem::create([
            'name' => 'Reporte de Pagos',
            'slug' => 'reporte-pagos',
            'parent' => $fact->id,
            'order' => 0,
            'description' => 'Reporte de Pagos',
            'route' => '/dashboard/reporte-pagos',
            'menu_id' => $menuBase->id,
        ]);

        MenuItem::create([
            'name' => 'Estado de Cuenta',
            'slug' => 'estado-cuenta',
            'parent' => $fact->id,
            'order' => 0,
            'description' => 'Estado de Cuenta',
            'route' => '/dashboard/status-account',
            'menu_id' => $menuBase->id,
        ]);

        MenuItem::create([
            'name' => 'Facturas por Pagar',
            'slug' => 'facturas-por-pagar',
            'parent' => $fact->id,
            'order' => 0,
            'description' => 'Facturas por Pagar',
            'route' => '/dashboard/facturas-por-pagar',
            'menu_id' => $menuBase->id,
        ]);
    }
}
