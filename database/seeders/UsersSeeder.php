<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UsersSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void {
    User::create([
      'email' => 'hof.cyrusbelanio+admin@gmail.com',
      'password' => 'Test!123',
      'role' => 'superadmin',
      'first_name' => 'Jones',
      'last_name' => 'Cyrus',
      'phone_number' => '639452685455',
      'birth_date' => '2024-12-11',
      'gender' => 'Male',
      'emergency_contact_name' => 'Test',
      'emergency_contact_no' => '639452685455',
      'address1' => 'Unknown St',
      'barangay' => 'Binukawan',
      'municipality' => 'Bagac',
      'province' => 'Bataan',
      'zip_code' => 2107
    ]);

    User::create([
      'email' => 'sample@bms.test',
      'password' => 'Test!123',
      'role' => 'superadmin',
      'first_name' => 'Sample',
      'last_name' => 'User',
      'phone_number' => '639814730001',
      'birth_date' => '2024-12-11',
      'gender' => 'Male',
      'emergency_contact_name' => 'Test',
      'emergency_contact_no' => '639814730001',
      'address1' => 'Unknown St',
      'barangay' => 'Ibayo',
      'municipality' => 'Balanga',
      'province' => 'Bataan',
      'zip_code' => 2103
    ]);


    User::factory(10)->create();
  }
}
