<?php

namespace Database\Factories;

use App\Models\Employee;
use App\Models\TimeEntry;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TimeEntry>
 */
class TimeEntryFactory extends Factory
{
  /**
   * Define the model's default state.
   *
   * @return array<string, mixed>
   */
  public function definition(): array {
    $employees = Employee::all();

    return [
      'user_id' => fake()->randomElement($employees),
      'type' => 'time_in',
      'datetime' => fake()->dateTime(),
      'metadata' => [],
      'is_synced' => false
    ];
  }
}
