<?php use Illuminate\Database\Eloquent\Model as Eloquent;

class VotoRespuesta extends Eloquent {
    protected $table = 'respuesta_votos';
    protected $visible = ['id', 'valor', 'created_at', 'updated_at'];
    protected $fillable = ['respuesta_id', 'usuario_id'];

    public function respuesta() {
        return $this->belongsTo('Respuesta');
    }

    public function usuario() {
        return $this->belongsTo('Usuario');
    }

}
