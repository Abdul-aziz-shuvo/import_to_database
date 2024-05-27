<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BufferExcel extends Model
{
    use HasFactory;
    protected $table = 'buffer';
    protected $guarded = [''];
}
