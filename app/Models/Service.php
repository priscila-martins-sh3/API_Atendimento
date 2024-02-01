<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;
    protected $fillable = [
        'nome_pessoa',
        'nome_cliente',
        'area',
        'tipo_atendimento',
        'nome_suporte',
        'retorno',
        'informacoes',     
        'data',
        'hora',
    ];
}
