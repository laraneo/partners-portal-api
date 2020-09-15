<?php

use App\PortalDepartmen;
use Illuminate\Database\Seeder;

class CreatePortalDeparmentTable extends Seeder
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
                'description' => 'Default',
                'telephone' => '000000000',
                'email' => 'default@default.com',
            ],
        ];
        foreach ($data as $element) {
            PortalDepartmen::create([
                'description' => $element['description'],
            ]);
        }
    }
}
