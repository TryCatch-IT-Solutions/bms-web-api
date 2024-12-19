<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 *
 *
 * @property int $id
 * @property int $user_id
 * @property string $type
 * @property string $datetime
 * @property array|null $metadata
 * @property int $is_synced
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property-read \App\Models\User $employee
 * @method static \Database\Factories\TimeEntryFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TimeEntry newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TimeEntry newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TimeEntry query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TimeEntry whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TimeEntry whereDatetime($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TimeEntry whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TimeEntry whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TimeEntry whereIsSynced($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TimeEntry whereMetadata($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TimeEntry whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TimeEntry whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TimeEntry whereUserId($value)
 * @mixin \Eloquent
 */
class TimeEntry extends Model {
  use HasFactory;

  protected $guarded = ['id'];

  protected function casts(): array {
    return [
      'metadata' => 'json'
    ];
  }

  public function employee(): BelongsTo {
    return $this->belongsTo(User::class, 'user_id');
  }
}
