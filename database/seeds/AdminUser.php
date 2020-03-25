<?php

use Illuminate\Database\Seeder;

class AdminUser extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $username = 'admin';
        $email = 'admin@example.com';
        $password = str_random(10);

        DB::table('users')->insert([
            'name' => $username,
            'email' => $email,
            'password' => Hash::make($password),
            'role' => 1,
        ]);

        echo 'Initialize Password For ' . $username . ': '. $password . "\n\n";
    }
}
