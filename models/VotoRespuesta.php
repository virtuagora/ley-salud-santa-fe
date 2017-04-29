<?php use Illuminate\Database\Eloquent\Model as Eloquent;

class VotoRespuesta extends Eloquent {
    protected $table = 'respuesta_votos';
    protected $visible = ['id', 'valor', 'created_at', 'updated_at', 'usuario'];
    protected $fillable = ['respuesta_id', 'usuario_id'];
    protected $with = ['usuario'];


    public function respuesta() {
        return $this->belongsTo('Respuesta');
    }

    public function usuario() {
        return $this->belongsTo('Usuario');
    }

}
