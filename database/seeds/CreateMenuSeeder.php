<?php

use App\Menu;
use App\Role;
use App\MenuItem;
use App\Parameter;
use App\MenuItemRole;
use Illuminate\Database\Seeder;

class CreateMenuSeeder extends Seeder
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

        Parameter::create([
            'description' => 'MENU PRINCIPAL',
            'parameter' => 'MENU_ID',
            'value' => $menuBase->id,
            'eliminable' => 1,
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
            'name' => 'Socios',
            'slug' => 'socios',
            'parent' => 0,
            'order' => 0,
            'description' => 'Socios',
            'route' => '/dashboard/partner',
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


        $sec = MenuItem::create([
            'name' => 'Seguridad',
            'slug' => 'seguridad',
            'parent' => 0,
            'order' => 1,
            'description' => 'Seguridad',
            'route' => '',
            'menu_id' => $menuBase->id,
        ]);

        MenuItem::create([
            'name' => 'Usuarios',
            'slug' => 'usuarios',
            'parent' => $sec->id,
            'order' => 2,
            'description' => 'Usuarios',
            'route' => '/dashboard/user',
            'menu_id' => $menuBase->id,
        ]);

        MenuItem::create([
            'name' => 'Roles',
            'slug' => 'roles',
            'parent' => $sec->id,
            'order' => 2,
            'description' => 'Roles',
            'route' => '/dashboard/role',
            'menu_id' => $menuBase->id,
        ]);

        MenuItem::create([
            'name' => 'Permisos',
            'slug' => 'permisos',
            'parent' => $sec->id,
            'order' => 3,
            'description' => 'Permisos',
            'route' => '/dashboard/permission',
            'menu_id' => $menuBase->id,
        ]);
        
        MenuItem::create([
            'name' => 'Parametros',
            'slug' => 'parametros',
            'parent' => $sec->id,
            'order' => 3,
            'description' => 'Parametros',
            'route' => '/dashboard/parameter',
            'menu_id' => $menuBase->id,
        ]);

        MenuItem::create([
            'name' => 'Widget',
            'slug' => 'widget',
            'parent' => $sec->id,
            'order' => 4,
            'description' => 'Widget',
            'route' => '/dashboard/widget',
            'menu_id' => $menuBase->id,
        ]);

        MenuItem::create([
            'name' => 'Menu',
            'slug' => 'menu',
            'parent' => $sec->id,
            'order' => 5,
            'description' => 'Menu',
            'route' => '/dashboard/menu',
            'menu_id' => $menuBase->id,
        ]);

        MenuItem::create([
            'name' => 'Menu Item',
            'slug' => 'menu-item',
            'parent' => $sec->id,
            'order' => 6,
            'description' => 'Menu',
            'route' => '/dashboard/menu-item',
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

        $data = [ 
            ['menuItem' =>  'inicio', 'roles' => ['promotor'] ],
            [ 'menuItem' => 'notas', 'roles' => ['promotor'] ],
            [ 'menuItem' => 'socios', 'roles' => ['promotor'] ],
            [ 'menuItem' => 'actualizacion-datos', 'roles' => ['socio'] ],
            ['menuItem' => 'seguridad', 'roles' => ['promotor']],
            ['menuItem' => 'usuarios    ', 'roles' => ['promotor']],
            ['menuItem' => 'roles', 'roles' => ['promotor']],
            ['menuItem' => 'permisos', 'roles' => ['promotor']],
            ['menuItem' => 'parametros', 'roles' => ['promotor']],
            ['menuItem' => 'widget', 'roles' => ['promotor']],
            ['menuItem' => 'menu', 'roles' => ['promotor']],
            ['menuItem' => 'menu-item', 'roles' => ['promotor']],
            [ 'menuItem' => 'facturacion', 'roles' => ['socio'] ],
            [ 'menuItem' => 'reporte-pagos', 'roles' => ['socio'] ],
            [ 'menuItem' => 'estado-cuenta', 'roles' => ['socio'] ],
            [ 'menuItem' => 'facturas-por-pagar', 'roles' => ['socio'] ],
        ];
        foreach ($data as $key => $value) {
            foreach ($value['roles'] as $key => $role) {
                $menuItem = MenuItem::where('slug', $value['menuItem'])->first();
                $role = Role::where('slug', $role)->first();
                MenuItemRole::create([
                    'role_id' => $role->id,
                    'menu_item_id' => $menuItem ? $menuItem->id : null,
                ]);
            }
        }
    }
}
