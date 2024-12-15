<?php

namespace Database\Seeders;

use App\Models\TimeEntry;
use Database\Factories\TimeEntryFactory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TimeEntriesSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void {
    TimeEntry::factory(10)->create();
  }
}
