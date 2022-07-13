<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContracts;
use Illuminate\Database\Eloquent\Builder;

class Account extends Model implements AuditableContracts
{
    use Auditable;
	use HasDateTimeFormatter;
    // FIX Search
    use Searchable;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'player_id',
    ];

    public function former_names()
    {
        return $this->hasMany(FormerName::class);
    }
    
    public function player()
    {
        return $this->belongsTo(Player::class)->withTrashed();
    }

    public function events()
    {
        return $this->morphToMany(Event::class, 'eventable')->withTimestamps();
    }
}
