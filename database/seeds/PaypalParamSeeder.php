<?php

use Illuminate\Database\Seeder;

use App\Parameter;

class PaypalParamSeeder extends Seeder
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
                'description' => 'Client ID paypal',
                'parameter' => 'PAYPAL_CLIENT_ID',
                'value' => 'Ab8frqGsF4rlmjIH9mS9kTdaGo2-vLh-v0PK5G1ZxeKBSTbAkygWF3eRCPYydHRtQBGlRJyLPDY4v5Aw',
                'eliminable' => 1,
            ],
            [
                'description' => 'Habilitar pago',
                'parameter' => 'HABILITAR_PAGO',
                'value' => 1,
                'eliminable' => 1,
            ],
        ];
        foreach ($data as $key => $value) {
            Parameter::create([
                'description' => $value['description'],
                'parameter' => $value['parameter'],
                'value' => $value['value'],
                'eliminable' => $value['eliminable'],
            ]);
        }
    }
}
