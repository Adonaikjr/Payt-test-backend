<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class table_redirectLog extends Model
{
    use HasFactory, SoftDeletes;
    public function redirect()
    {
        return $this->belongsTo(table_redirects::class, 'redirect_id');
    }
    protected $fillable = [
        'redirect_id',
        'ip',
        'user_agent',
        'referer',
        'query_params',
        'date_time_acess',
    ];
}
