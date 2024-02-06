<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Support extends Model
{
    use HasFactory;

    protected $fillable = [
        'area_atuacao',
        'livre',       
    ];
    
    public function user()
    {
	return $this->belongsTo(User::class);
    }
}

