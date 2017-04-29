<?php use Illuminate\Database\Eloquent\Model as Eloquent;

class Respuesta extends Eloquent {
    protected $table = 'respuestas';
    protected $visible = ['id', 'usuario', 'valoracion', 'respuestas', 'comentarios', 'puntos','votos'];
    protected $fillable = ['eje_id', 'usuario_id'];
    protected $with = ['usuario', 'comentarios','votos'];

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

    public function votos() {
        return $this->hasMany('VotoRespuesta');
    }

    public function getRaizAttribute() {
        return $this->eje;
    }

    public static function boot() {
        parent::boot();
        static::deleting(function($rta) {
            foreach ($rta->comentarios as $comentario) {
                $comentario->delete();
            }
            return true;
        });
    }
}
