<?php use Illuminate\Database\Eloquent\Model as Eloquent;

class Respuesta extends Eloquent {
    protected $table = 'respuestas';
    protected $visible = ['id', 'usuario', 'valoracion', 'respuestas', 'puntos'];
    protected $fillable = ['eje_id', 'usuario_id'];
    protected $with = ['usuario'];

    public function setRespuestasAttribute($value) {
        $this->attributes['respuestas'] = json_encode($value);
    }

    public function getRespuestasAttribute($value) {
        return json_decode($value, true);
    }

    public function eje() {
        return $this->belongsTo('Eje');
    }

    public function usuario() {
        return $this->belongsTo('Usuario');
    }

    public function comentarios() {
        return $this->morphMany('Comentario', 'comentable')->orderBy('updated_at', 'DESC');
    }

    public function getRaizAttribute() {
        return $this->eje;
    }

}
