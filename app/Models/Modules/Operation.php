<?php
namespace App\Models\Modules;

use Illuminate\Database\Eloquent\Model;

class Operation extends Model
{
    protected $table = 'operations';

    protected $fillable = [
        'name'
    ];
}
