<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Group extends Model {
  protected $guarded = ['id'];

  public function devices(): HasMany {
    return $this->hasMany(Device::class);
  }

  public function users(): HasManyThrough {
    return $this->hasManyThrough(User::class, Device::class);
  }
}
