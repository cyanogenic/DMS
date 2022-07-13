<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;

use Illuminate\Database\Eloquent\Model;

class FormerName extends Model
{
	use HasDateTimeFormatter;

    protected $table = 'former_names';
    
    protected $fillable = [
        'account_id',
        'name',
    ];
}
