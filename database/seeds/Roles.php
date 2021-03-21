<?php

use Illuminate\Database\Seeder;

class Roles extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \App\Models\Roles::create(array(
                'name' => 'Shipper',
                'description' => 'Shipper user'
            )
        );
        \App\Models\Roles::create(array(
                'name' => 'Carrier',
                'description' => 'Carrier user'
            )
        );
    }
}
