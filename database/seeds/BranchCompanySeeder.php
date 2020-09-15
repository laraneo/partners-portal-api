<?php

use App\BranchCompany;
use Illuminate\Database\Seeder;

class BranchCompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = [
            [ 'description' => 'Compañia 1' ],
            [ 'description' => 'Compañia 2'],
            [ 'description' => 'Compañia 3'],
        ];
        foreach ($data as $element) {
            BranchCompany::create([
                'description' => $element['description'],
            ]);
        }
    }
}
