<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 *
 *
 * @property int $id
 * @property string $name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Device> $devices
 * @property-read int|null $devices_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $users
 * @property-read int|null $users_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Group newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Group newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Group query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Group whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Group whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Group whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Group whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Group whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Group extends Model {
  protected $guarded = ['id'];

  protected $with = ['devices', 'groupAdmin', 'employees'];
  /**
   * Retrieve all devices
   */
  public function devices(): HasMany {
    return $this->hasMany(Device::class);
  }

  /**
   * Retrieve all users for the group.
   */
  public function employees(): HasMany {
    return $this->hasMany(User::class)->where('role', 'employee');
  }

  /**
   * Retrieve the group admin for a particular group.
   */
  public function groupAdmin(): HasOne {
    return $this->hasOne(User::class)->select(['id', 'group_id', 'first_name', 'last_name'])->where('role', 'groupadmin');
  }
}
