<?php

class Eje extends Contenible {
    protected $table = 'ejes';
    protected $dates = ['deleted_at'];
    protected $visible = ['id', 'descripcion', 'preguntas', 'links'];

    public function secciones() {
        return $this->hasMany('Seccion');
    }

    public function setPreguntasAttribute($value) {
        $this->attributes['preguntas'] = json_encode($value);
    }

    public function getPreguntasAttribute($value) {
        return json_decode($value, true);
    }

    public function setLinksAttribute($value) {
        $this->attributes['links'] = json_encode($value);
    }

    public function getLinksAttribute($value) {
        return json_decode($value, true);
    }
    
    public function respuestas() {
        return $this->hasMany('Respuesta');
    }
}
