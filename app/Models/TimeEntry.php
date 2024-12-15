<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TimeEntry extends Model {
  use HasFactory;

  protected $guarded = ['id'];

  protected function casts(): array {
    return [
      'metadata' => 'json'
    ];
  }

  public function employee(): BelongsTo {
    return $this->belongsTo(User::class);
  }

}
