<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Member extends Model
{
	use HasDateTimeFormatter;

	use SoftDeletes;
	
	public function alias() { return $this->hasMany(Alias::class); }
}
