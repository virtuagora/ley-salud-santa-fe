<?php use Illuminate\Database\Eloquent\Model as Eloquent;

class Principio extends Eloquent {

    protected $table = 'principios';
    protected $visible = ['id', 'orden', 'titulo', 'descripcion'];

}
