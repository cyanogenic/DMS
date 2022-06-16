<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;

use Illuminate\Database\Eloquent\Model;

class OCRResult extends Model
{
	use HasDateTimeFormatter;
    protected $table = 'ocr_results';
    
    protected $fillable = [
        'image',
        'md5',
        'res',
    ];
}
