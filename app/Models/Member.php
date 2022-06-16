<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;

class Member extends Model
{
	use HasDateTimeFormatter;
	use Searchable;
	use SoftDeletes;

	protected $table = 'members';

	protected $fillable = 
	[
		'nickname',
        'name',
		'dkp',
		'innercity',
	];
	
	public function alias() { return $this->hasMany(Alias::class); }

	public function toSearchableArray()
    {
        return [ 'name' => $this->name ];
    }
}
