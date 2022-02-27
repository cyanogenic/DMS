<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;

use Illuminate\Database\Eloquent\Model;

class Alias extends Model
{
	use HasDateTimeFormatter;
    protected $table = 'alias';
    
    public function member() { return $this->belongsTo(Member::class); }
}
