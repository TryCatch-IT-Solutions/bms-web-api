<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 
 *
 * @property-read \App\Models\Group|null $group
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Device newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Device newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Device onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Device query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Device withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Device withoutTrashed()
 * @mixin \Eloquent
 */
class Device extends Model {
  use SoftDeletes;

  protected $guarded = ['id'];

  public function group(): BelongsTo {
    return $this->belongsTo(Group::class);
  }
}
