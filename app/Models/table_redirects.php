<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class table_redirects extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'codigo',
        'status',
        'url_destino',
        'ultimo_acesso',
    ];
}
