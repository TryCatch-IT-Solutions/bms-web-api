<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Device extends Model {
  use SoftDeletes;

  protected $guarded = ['id'];

  public function group(): BelongsTo {
    return $this->belongsTo(Group::class);
  }
}
