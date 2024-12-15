<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Employee extends User {
  protected $table = 'users';

  public static function booted(): void {
    static::addGlobalScope('employee', function (Builder $query) {
      $query->where('role', 'employee');
    });
  }

  /**
   * Pulls up all the time entries related to this employee.
   */
  public function timeEntries(): HasMany {
    return $this->hasMany(TimeEntry::class, 'user_id');
  }
}
