<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;
use Dcat\Admin\Models\Administrator;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Event extends Model
{
	use HasDateTimeFormatter;

	public function scoring() { return $this->belongsTo(Scoring::class); }
    public function member() { return $this->belongsToMany(Member::class); }
	public function admin_user() { return $this->belongsTo(Administrator::class); }
}
