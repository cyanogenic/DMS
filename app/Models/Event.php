<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContracts;

class Event extends Model implements AuditableContracts
{
	use Auditable;
	use HasDateTimeFormatter;

	public function scoring()
	{
		return $this->belongsTo(Scoring::class);
	}

    public function accounts()
	{
		return $this->morphedByMany(Account::class, 'eventable')->withTrashed()->withTimestamps();
	}

	public function players()
	{
		return $this->morphedByMany(Player::class, 'eventable')->withTrashed()->withTimestamps();
	}
}
