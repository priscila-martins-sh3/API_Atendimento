<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Support;
use App\Models\Contact;
use Illuminate\Database\Eloquent\SoftDeletes;

class Service extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'support_id',
        'contact_id',
        'tipo_servico',
        'retorno',
        'informacoes',  

    ];

    public static $tiposPermitidosServico = ['tirar_duvida', 'informar_problema', 'solicitar_recurso'];
    public static function tiposValidosServico()
    {
        return implode(',', self::$tiposPermitidosServico);
    }

    public function support()
    {
	return $this->belongsTo(Support::class);
    }

    public function contact()
    {
	return $this->belongsTo(Contact::class));
    }
}

