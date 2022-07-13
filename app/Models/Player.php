<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContracts;

class Player extends Model implements AuditableContracts
{
    use Auditable;
	use HasDateTimeFormatter;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'dkp',
    ];

    public function accounts()
    {
        return $this->hasMany(Account::class)->withTrashed();
    }

    public function events()
    {
        return $this->morphToMany(Event::class, 'eventable')->withTimestamps();
    }
}
