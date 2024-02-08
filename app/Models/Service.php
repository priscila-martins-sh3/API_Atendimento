<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Support;
use App\Models\Contact;

class Service extends Model
{
    use HasFactory;
    protected $fillable = [
        'support_id',
        'contact_id',
        'tipo_servico',
        'retorno',
        'informacoes',  

    ];

    public static $tiposPermitidosAtendimento = ['tirar_duvida', 'informar_problema', 'solicitar_recurso'];
    public static function tiposValidosAtendimento()
    {
        return implode(',', self::$tiposAtendimento);
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

