<?php

namespace Database\Factories;

use App\Models\User;
use Faker\Provider\en_PH\Address;
use Faker\Provider\en_PH\PhoneNumber;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory {

  /**
   * Define the model's default state.
   *
   * @return array<string, mixed>
   */
  public function definition(): array
  {
    $roles = [
      'superadmin' => 1,  // 1x weight
      'groupadmin' => 2,  // 2x weight
      'employee' => 7,    // 7x weight
    ];

    $role = $this->faker->randomElement(array_merge(...array_map(
      fn ($role, $weight) => array_fill(0, $weight, $role),
      array_keys($roles),
      $roles
    )));

    fake()->addProvider(Address::class);
    fake()->addProvider(PhoneNumber::class);

    return [
      'email' => fake()->unique()->safeEmail(),
      'password' => 'Test!123',
      'role' => $role,
      'first_name' => fake()->firstName(),
      'last_name' => fake()->lastName(),
      'phone_number' => preg_replace('/\D/', '', fake('en_PH')->mobileNumber()),
      'birth_date' => fake()->date(),
      'gender' => fake()->randomElement(['male', 'female']),
      'emergency_contact_name' => fake('en_PH')->name(),
      'emergency_contact_no' => fake('en_PH')->mobileNumber(),
      'address1' => explode(',', fake('en_PH')->address())[0],
      'barangay' => fake('en_PH')->barangay(),
      'province' => fake('en_PH')->province(),
      'municipality' => fake('en_PH')->municipality(),
      'zip_code' => fake('en_PH')->postcode()
    ];
  }

  /**
   * Indicate that the model's email address should be unverified.
   */
  public function unverified(): static
  {
    return $this->state(fn(array $attributes) => [
      'email_verified_at' => null,
    ]);
  }
}
