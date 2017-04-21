<?php

require __DIR__.'/../vendor/autoload.php';
require __DIR__.'/../app/database.php';

use Illuminate\Database\Capsule\Manager as Capsule;

?><!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Asistente de instalación Virtuágora</title>
    <link rel="stylesheet" href="assets-salud/css/salud.css" />
</head>
<body>
<div class="container" style="margin-top:20px";>
<div class="row">
<div class="col-sm-6 col-sm-offset-3">
<div class="panel panel-primary">
<div class="panel-heading">
    <h3 class="panel-title">Instalar Virtuagora</h3>
  </div>
  <div class="panel-body">

<?php if(isset($_POST['submit'])) {
$titulo = '¡Virtuágora se ha instalado exitosamente!';
$descrp = 'Ya puede comenzar a utilizar la plataforma, pero primero elimine este archivo para evitar inconvenientes de seguridad.';
$exito = true;
try {
    if (Capsule::schema()->hasTable('ajustes')) {
        $titulo = '¡Ha ocurrido un error!';
        $descrp = 'La plataforma parece ya estar instalada.';
        $exito = true;
    } else {
        Capsule::schema()->create('ajustes', function($table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('key')->unique();
            $table->string('value_type');
            $table->integer('int_value')->nullable();
            $table->string('str_value')->nullable();
            $table->text('txt_value')->nullable();
            $table->string('description');
            $table->timestamps();
        });
        Capsule::schema()->create('nodos', function($table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('region')->unsigned();
            $table->string('nombre');
        });
        Capsule::schema()->create('departamentos', function($table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('nodo_id')->unsigned();
            $table->string('nombre');
            $table->foreign('nodo_id')->references('id')->on('nodos')->onDelete('cascade');
        });
        Capsule::schema()->create('localidades', function($table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('departamento_id')->unsigned();
            $table->string('nombre');
            $table->foreign('departamento_id')->references('id')->on('departamentos')->onDelete('cascade');
        });
        Capsule::schema()->create('usuarios', function($table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('nombre');
            $table->string('apellido');
            $table->dateTime('nacimiento')->nullable();
            $table->string('genero')->nullable();
            $table->string('institucion')->nullable();
            $table->string('ocupacion')->nullable();
            $table->integer('localidad_id')->unsigned()->nullable();
            $table->string('extra')->nullable();
            $table->integer('img_tipo')->unsigned();
            $table->string('img_hash');
            $table->string('huella')->nullable();
            $table->integer('puntos')->default(0);
            $table->string('advertencia')->nullable();
            $table->boolean('suspendido')->default(0);
            $table->boolean('es_funcionario')->default(0);
            $table->boolean('es_jefe')->default(0);
            $table->string('dni')->nullable();
            $table->string('token')->nullable()->default(null);
            $table->timestamp('verified_at')->nullable();
            $table->timestamp('fin_advertencia')->nullable();
            $table->timestamp('fin_suspension')->nullable();
            $table->integer('partido_id')->unsigned()->nullable();
            $table->integer('patrulla_id')->unsigned()->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
        Capsule::schema()->create('preusuarios', function($table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('nombre');
            $table->string('apellido');
            $table->string('emailed_token');
            $table->dateTime('nacimiento')->nullable();
            $table->string('genero')->nullable();
            $table->string('institucion')->nullable();
            $table->string('ocupacion')->nullable();
            $table->integer('localidad_id')->unsigned()->nullable();
            $table->string('extra')->nullable();
            $table->timestamps();
        });
        Capsule::schema()->create('acciones', function($table) {
            $table->engine = 'InnoDB';
            $table->string('id', 10)->primary();
            $table->string('nombre');
        });
        Capsule::schema()->create('userlogs', function($table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->morphs('objeto');
            $table->string('accion_id', 10);
            $table->integer('actor_id')->unsigned();
            $table->foreign('actor_id')->references('id')->on('usuarios')->onDelete('cascade');
            $table->timestamps();
        });
        Capsule::schema()->create('patrullas', function($table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('nombre');
            $table->text('descripcion');
            $table->timestamps();
        });
        Capsule::schema()->create('poderes', function($table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('nombre');
            $table->string('descripcion');
        });
        Capsule::schema()->create('patrulla_poder', function($table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('patrulla_id')->unsigned();
            $table->integer('poder_id')->unsigned();
            $table->foreign('patrulla_id')->references('id')->on('patrullas')->onDelete('cascade');
            $table->foreign('poder_id')->references('id')->on('poderes')->onDelete('cascade');
        });
        Capsule::schema()->create('adminlogs', function($table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('descripcion');
            $table->string('subclase');
            $table->morphs('objeto');
            $table->integer('poder_id')->unsigned();
            $table->integer('actor_id')->unsigned();
            $table->foreign('actor_id')->references('id')->on('usuarios')->onDelete('cascade');
            $table->timestamps();
        });
        Capsule::schema()->create('contenidos', function($table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->morphs('contenible');
            $table->string('titulo');
            $table->integer('orden');
            $table->text('resumen');
            $table->string('huella')->nullable();
            $table->integer('puntos')->unsigned()->default(0);
            $table->integer('autor_id')->unsigned();
            $table->foreign('autor_id')->references('id')->on('usuarios')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
        });
        Capsule::schema()->create('ejes', function($table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->text('descripcion');
            $table->text('links')->nullable();
            $table->text('preguntas')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
        Capsule::schema()->create('respuestas', function($table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('valoracion');
            $table->text('respuestas')->nullable();
            $table->integer('puntos')->default(0);
            $table->integer('usuario_id')->unsigned();
            $table->foreign('usuario_id')->references('id')->on('usuarios')->onDelete('cascade');
            $table->integer('eje_id')->unsigned();
            $table->foreign('eje_id')->references('id')->on('ejes')->onDelete('cascade');
            $table->timestamps();
        });
        Capsule::schema()->create('eventos', function($table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('titulo');
            $table->string('huella')->nullable();
            $table->integer('autor_id')->unsigned();
            $table->foreign('autor_id')->references('id')->on('usuarios')->onDelete('cascade');
            $table->text('descripcion');
            $table->string('lugar');
            $table->string('info')->nullable();
            $table->date('fecha_desde');
            $table->date('fecha_hasta')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
        Capsule::schema()->create('comentarios', function($table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->morphs('comentable');
            $table->text('cuerpo');
            $table->integer('votos')->default(0);
            $table->integer('autor_id')->unsigned();
            $table->foreign('autor_id')->references('id')->on('usuarios')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
        });
        Capsule::schema()->create('comentario_votos', function($table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('valor');
            $table->integer('usuario_id')->unsigned();
            $table->integer('comentario_id')->unsigned();
            $table->foreign('comentario_id')->references('id')->on('comentarios')->onDelete('cascade');
            $table->timestamps();
        });
        Capsule::schema()->create('principios', function($table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('titulo');
            $table->text('descripcion');
            $table->integer('orden');
            $table->timestamps();
        });
        Capsule::table('nodos')->insert([
            ['region' => 1, 'nombre' => 'Reconquista'],
            ['region' => 2, 'nombre' => 'Rafaela'],
            ['region' => 3, 'nombre' => 'Santa Fe'],
            ['region' => 4, 'nombre' => 'Rosario'],
            ['region' => 5, 'nombre' => 'Venado Tuerto'],
        ]);
        Capsule::table('departamentos')->insert([
            ['nodo_id' => 1, 'nombre' => 'General Obligado'],
            ['nodo_id' => 1, 'nombre' => 'San Javier'],
            ['nodo_id' => 1, 'nombre' => 'Vera'],
            ['nodo_id' => 2, 'nombre' => '9 de Julio'],
            ['nodo_id' => 2, 'nombre' => 'Castellanos'],
            ['nodo_id' => 2, 'nombre' => 'San Cristobal'],
            ['nodo_id' => 2, 'nombre' => 'San Martín'],
            ['nodo_id' => 3, 'nombre' => 'Garay'],
            ['nodo_id' => 3, 'nombre' => 'La Capital'],
            ['nodo_id' => 3, 'nombre' => 'Las Colonias'],
            ['nodo_id' => 3, 'nombre' => 'San Javier'],
            ['nodo_id' => 3, 'nombre' => 'San Jerónimo'],
            ['nodo_id' => 3, 'nombre' => 'San Justo'],
            ['nodo_id' => 4, 'nombre' => 'Belgrano'],
            ['nodo_id' => 4, 'nombre' => 'Caseros'],
            ['nodo_id' => 4, 'nombre' => 'Constitución'],
            ['nodo_id' => 4, 'nombre' => 'Iriondo'],
            ['nodo_id' => 4, 'nombre' => 'Rosario'],
            ['nodo_id' => 4, 'nombre' => 'San Jerónimo'],
            ['nodo_id' => 4, 'nombre' => 'San Lorenzo'],
            ['nodo_id' => 4, 'nombre' => 'San Martín'],
            ['nodo_id' => 5, 'nombre' => 'Caseros'],
            ['nodo_id' => 5, 'nombre' => 'Constitución'],
            ['nodo_id' => 5, 'nombre' => 'General Lopez'],
        ]);
        Capsule::table('localidades')->insert([
['departamento_id' => 1, 'nombre' => 'Arroyo Ceibal'],
['departamento_id' => 1, 'nombre' => 'Avellaneda'],
['departamento_id' => 1, 'nombre' => 'Berna'],
['departamento_id' => 1, 'nombre' => 'El Araza'],
['departamento_id' => 1, 'nombre' => 'El Rabon'],
['departamento_id' => 1, 'nombre' => 'El Sombrerito'],
['departamento_id' => 1, 'nombre' => 'Florencia'],
['departamento_id' => 1, 'nombre' => 'Guadalupe Norte'],
['departamento_id' => 1, 'nombre' => 'Ing. Chanourdie'],
['departamento_id' => 1, 'nombre' => 'La Sarita'],
['departamento_id' => 1, 'nombre' => 'Lanteri'],
['departamento_id' => 1, 'nombre' => 'Las Garzas'],
['departamento_id' => 1, 'nombre' => 'Las Toscas'],
['departamento_id' => 1, 'nombre' => 'Los Laureles'],
['departamento_id' => 1, 'nombre' => 'Malabrigo'],
['departamento_id' => 1, 'nombre' => 'Nicanor Molinas'],
['departamento_id' => 1, 'nombre' => 'Reconquista'],
['departamento_id' => 1, 'nombre' => 'San Antonio De Obligado'],
['departamento_id' => 1, 'nombre' => 'Tacuarendí'],
['departamento_id' => 1, 'nombre' => 'Villa Ana'],
['departamento_id' => 1, 'nombre' => 'Villa Guillermina'],
['departamento_id' => 1, 'nombre' => 'Villa Ocampo'],
['departamento_id' => 1, 'nombre' => 'Otro...'],
['departamento_id' => 2, 'nombre' => 'Alejandra'],
['departamento_id' => 2, 'nombre' => 'Colonia Duran'],
['departamento_id' => 2, 'nombre' => 'Romang'],
['departamento_id' => 2, 'nombre' => 'Otro...'],
['departamento_id' => 3, 'nombre' => 'Calchaquí'],
['departamento_id' => 3, 'nombre' => 'Cañada Ombú'],
['departamento_id' => 3, 'nombre' => 'Fortin Olmos'],
['departamento_id' => 3, 'nombre' => 'Garabato'],
['departamento_id' => 3, 'nombre' => 'Golondrina'],
['departamento_id' => 3, 'nombre' => 'Intiyaco'],
['departamento_id' => 3, 'nombre' => 'La Gallareta'],
['departamento_id' => 3, 'nombre' => 'Los Amores'],
['departamento_id' => 3, 'nombre' => 'Margarita'],
['departamento_id' => 3, 'nombre' => 'Tartagal'],
['departamento_id' => 3, 'nombre' => 'Toba'],
['departamento_id' => 3, 'nombre' => 'Vera'],
['departamento_id' => 3, 'nombre' => 'Otro...'],
['departamento_id' => 4, 'nombre' => 'Esteban Rams'],
['departamento_id' => 4, 'nombre' => 'Gato Colorado'],
['departamento_id' => 4, 'nombre' => 'Gregoria Perez De Denis'],
['departamento_id' => 4, 'nombre' => 'Juan De Garay'],
['departamento_id' => 4, 'nombre' => 'Logroño'],
['departamento_id' => 4, 'nombre' => 'Montefiore'],
['departamento_id' => 4, 'nombre' => 'Pozo Borrado'],
['departamento_id' => 4, 'nombre' => 'San Bernardo'],
['departamento_id' => 4, 'nombre' => 'Santa Margarita'],
['departamento_id' => 4, 'nombre' => 'Tostado'],
['departamento_id' => 4, 'nombre' => 'Villa Minetti'],
['departamento_id' => 4, 'nombre' => 'Otro'],
['departamento_id' => 5, 'nombre' => 'Angélica'],
['departamento_id' => 5, 'nombre' => 'Ataliva'],
['departamento_id' => 5, 'nombre' => 'Aurelia'],
['departamento_id' => 5, 'nombre' => 'Bauer Y Sigel'],
['departamento_id' => 5, 'nombre' => 'Bella Italia'],
['departamento_id' => 5, 'nombre' => 'Colonia Aldao'],
['departamento_id' => 5, 'nombre' => 'Colonia Bicha'],
['departamento_id' => 5, 'nombre' => 'Colonia Bigand'],
['departamento_id' => 5, 'nombre' => 'Colonia Castellanos'],
['departamento_id' => 5, 'nombre' => 'Colonia Cello'],
['departamento_id' => 5, 'nombre' => 'Colonia Iturraspe'],
['departamento_id' => 5, 'nombre' => 'Colonia Margarita'],
['departamento_id' => 5, 'nombre' => 'Colonia Maua'],
['departamento_id' => 5, 'nombre' => 'Colonia Raquel'],
['departamento_id' => 5, 'nombre' => 'Coronel Fraga'],
['departamento_id' => 5, 'nombre' => 'Egusquiza'],
['departamento_id' => 5, 'nombre' => 'Esmeralda'],
['departamento_id' => 5, 'nombre' => 'Estación Clucellas'],
['departamento_id' => 5, 'nombre' => 'Estación Saguier'],
['departamento_id' => 5, 'nombre' => 'Eusebia'],
['departamento_id' => 5, 'nombre' => 'Eustolia'],
['departamento_id' => 5, 'nombre' => 'Fidela'],
['departamento_id' => 5, 'nombre' => 'Frontera'],
['departamento_id' => 5, 'nombre' => 'Galisteo'],
['departamento_id' => 5, 'nombre' => 'Garibaldi'],
['departamento_id' => 5, 'nombre' => 'Hugentobler'],
['departamento_id' => 5, 'nombre' => 'Humberto Primo'],
['departamento_id' => 5, 'nombre' => 'Josefina'],
['departamento_id' => 5, 'nombre' => 'Lehmann'],
['departamento_id' => 5, 'nombre' => 'Maria Juana'],
['departamento_id' => 5, 'nombre' => 'Plaza Clucellas'],
['departamento_id' => 5, 'nombre' => 'Presidente Roca'],
['departamento_id' => 5, 'nombre' => 'Pueblo Marini'],
['departamento_id' => 5, 'nombre' => 'Rafaela'],
['departamento_id' => 5, 'nombre' => 'Ramona'],
['departamento_id' => 5, 'nombre' => 'San Antonio'],
['departamento_id' => 5, 'nombre' => 'San Vicente'],
['departamento_id' => 5, 'nombre' => 'Santa Clara De Saguier'],
['departamento_id' => 5, 'nombre' => 'Sunchales'],
['departamento_id' => 5, 'nombre' => 'Susana'],
['departamento_id' => 5, 'nombre' => 'Tacural'],
['departamento_id' => 5, 'nombre' => 'Tacurales'],
['departamento_id' => 5, 'nombre' => 'Vila'],
['departamento_id' => 5, 'nombre' => 'Villa San Jose'],
['departamento_id' => 5, 'nombre' => 'Virginia'],
['departamento_id' => 5, 'nombre' => 'Zenon Pereyra'],
['departamento_id' => 5, 'nombre' => 'Otro...'],
['departamento_id' => 6, 'nombre' => 'Aguará Grande'],
['departamento_id' => 6, 'nombre' => 'Ambrosetti'],
['departamento_id' => 6, 'nombre' => 'Arrufo'],
['departamento_id' => 6, 'nombre' => 'Capivara'],
['departamento_id' => 6, 'nombre' => 'Ceres'],
['departamento_id' => 6, 'nombre' => 'Colonia Ana'],
['departamento_id' => 6, 'nombre' => 'Colonia Bossi'],
['departamento_id' => 6, 'nombre' => 'Colonia La Clara'],
['departamento_id' => 6, 'nombre' => 'Colonia Rosa'],
['departamento_id' => 6, 'nombre' => 'Constanza'],
['departamento_id' => 6, 'nombre' => 'Curupaity'],
['departamento_id' => 6, 'nombre' => 'Dos Rosas Y La Legua'],
['departamento_id' => 6, 'nombre' => 'Hersilia'],
['departamento_id' => 6, 'nombre' => 'Huanqueros'],
['departamento_id' => 6, 'nombre' => 'La Cabral'],
['departamento_id' => 6, 'nombre' => 'La Lucila'],
['departamento_id' => 6, 'nombre' => 'La Rubia'],
['departamento_id' => 6, 'nombre' => 'Las Avispas'],
['departamento_id' => 6, 'nombre' => 'Las Palmeras'],
['departamento_id' => 6, 'nombre' => 'Moises Ville'],
['departamento_id' => 6, 'nombre' => 'Monigotes'],
['departamento_id' => 6, 'nombre' => 'Monte Oscuridad'],
['departamento_id' => 6, 'nombre' => 'Ñanducita'],
['departamento_id' => 6, 'nombre' => 'Palacios'],
['departamento_id' => 6, 'nombre' => 'Portugalete'],
['departamento_id' => 6, 'nombre' => 'San Cristóbal'],
['departamento_id' => 6, 'nombre' => 'San Guillermo'],
['departamento_id' => 6, 'nombre' => 'Santurce'],
['departamento_id' => 6, 'nombre' => 'Soledad'],
['departamento_id' => 6, 'nombre' => 'Suardi'],
['departamento_id' => 6, 'nombre' => 'Villa Saralegui'],
['departamento_id' => 6, 'nombre' => 'Villa Trinidad'],
['departamento_id' => 6, 'nombre' => 'Otro...'],
['departamento_id' => 7, 'nombre' => 'Castelar'],
['departamento_id' => 7, 'nombre' => 'Crispi'],
['departamento_id' => 7, 'nombre' => 'Las Petacas'],
['departamento_id' => 7, 'nombre' => 'San Jorge'],
['departamento_id' => 7, 'nombre' => 'San Martín De Las Escobas'],
['departamento_id' => 7, 'nombre' => 'Sastre'],
['departamento_id' => 7, 'nombre' => 'Traill'],
['departamento_id' => 7, 'nombre' => 'Otro...'],
['departamento_id' => 8, 'nombre' => 'Cayasta'],
['departamento_id' => 8, 'nombre' => 'Colonia Mascias'],
['departamento_id' => 8, 'nombre' => 'Helvecia'],
['departamento_id' => 8, 'nombre' => 'Saladero Mariano Cabal'],
['departamento_id' => 8, 'nombre' => 'Santa Rosa De Calchines'],
['departamento_id' => 8, 'nombre' => 'Otro...'],
['departamento_id' => 9, 'nombre' => 'Arroyo Aguiar'],
['departamento_id' => 9, 'nombre' => 'Arroyo Leyes'],
['departamento_id' => 9, 'nombre' => 'Cabal'],
['departamento_id' => 9, 'nombre' => 'Campo Andino'],
['departamento_id' => 9, 'nombre' => 'Candioti'],
['departamento_id' => 9, 'nombre' => 'Emilia'],
['departamento_id' => 9, 'nombre' => 'Laguna Paiva'],
['departamento_id' => 9, 'nombre' => 'Llambi Campbell'],
['departamento_id' => 9, 'nombre' => 'Monte Vera'],
['departamento_id' => 9, 'nombre' => 'Nelson'],
['departamento_id' => 9, 'nombre' => 'Recreo'],
['departamento_id' => 9, 'nombre' => 'San José Del Rincon'],
['departamento_id' => 9, 'nombre' => 'Santa Fe'],
['departamento_id' => 9, 'nombre' => 'Santo Tomé'],
['departamento_id' => 9, 'nombre' => 'Sauce Viejo'],
['departamento_id' => 9, 'nombre' => 'Otro...'],
['departamento_id' => 10, 'nombre' => 'Colonia Cavour'],
['departamento_id' => 10, 'nombre' => 'Colonia San Jose'],
['departamento_id' => 10, 'nombre' => 'Cululú'],
['departamento_id' => 10, 'nombre' => 'Elisa'],
['departamento_id' => 10, 'nombre' => 'Empalme San Carlos'],
['departamento_id' => 10, 'nombre' => 'Esperanza'],
['departamento_id' => 10, 'nombre' => 'Felicia'],
['departamento_id' => 10, 'nombre' => 'Franck'],
['departamento_id' => 10, 'nombre' => 'Grutly'],
['departamento_id' => 10, 'nombre' => 'Hipatia'],
['departamento_id' => 10, 'nombre' => 'Humboldt'],
['departamento_id' => 10, 'nombre' => 'Ituzaingo'],
['departamento_id' => 10, 'nombre' => 'Jacinto L. Arauz'],
['departamento_id' => 10, 'nombre' => 'La Pelada'],
['departamento_id' => 10, 'nombre' => 'Las Tunas'],
['departamento_id' => 10, 'nombre' => 'Maria Luisa'],
['departamento_id' => 10, 'nombre' => 'Matilde'],
['departamento_id' => 10, 'nombre' => 'Nuevo Torino'],
['departamento_id' => 10, 'nombre' => 'Pilar'],
['departamento_id' => 10, 'nombre' => 'Progreso'],
['departamento_id' => 10, 'nombre' => 'Providencia'],
['departamento_id' => 10, 'nombre' => 'Pujato Norte'],
['departamento_id' => 10, 'nombre' => 'Rivadavia'],
['departamento_id' => 10, 'nombre' => 'Sa Pereira'],
['departamento_id' => 10, 'nombre' => 'San Agustín'],
['departamento_id' => 10, 'nombre' => 'San Carlos Centro'],
['departamento_id' => 10, 'nombre' => 'San Carlos Norte'],
['departamento_id' => 10, 'nombre' => 'San Carlos Sud'],
['departamento_id' => 10, 'nombre' => 'San Jerónimo  Norte'],
['departamento_id' => 10, 'nombre' => 'San Jeronimo Del Sauce'],
['departamento_id' => 10, 'nombre' => 'San Mariano'],
['departamento_id' => 10, 'nombre' => 'Santa Clara De Buena Vista'],
['departamento_id' => 10, 'nombre' => 'Santa María Centro'],
['departamento_id' => 10, 'nombre' => 'Santa Maria Norte'],
['departamento_id' => 10, 'nombre' => 'Santo Domingo'],
['departamento_id' => 10, 'nombre' => 'Sarmiento'],
['departamento_id' => 10, 'nombre' => 'Soutomayor'],
['departamento_id' => 10, 'nombre' => 'Otro...'],
['departamento_id' => 11, 'nombre' => 'Cacique Ariacaiquin'],
['departamento_id' => 11, 'nombre' => 'Colonia Teresa'],
['departamento_id' => 11, 'nombre' => 'La Brava'],
['departamento_id' => 11, 'nombre' => 'San Javier'],
['departamento_id' => 11, 'nombre' => 'Otro...'],
['departamento_id' => 12, 'nombre' => 'Arocena'],
['departamento_id' => 12, 'nombre' => 'Barrancas'],
['departamento_id' => 12, 'nombre' => 'Bernardo De Irigoyen'],
['departamento_id' => 12, 'nombre' => 'Campo Piaggio'],
['departamento_id' => 12, 'nombre' => 'Casalegno'],
['departamento_id' => 12, 'nombre' => 'Coronda'],
['departamento_id' => 12, 'nombre' => 'Desvio Arijon'],
['departamento_id' => 12, 'nombre' => 'Diaz'],
['departamento_id' => 12, 'nombre' => 'Gaboto'],
['departamento_id' => 12, 'nombre' => 'Gálvez'],
['departamento_id' => 12, 'nombre' => 'Gessler'],
['departamento_id' => 12, 'nombre' => 'Irigoyen (PUEBLO)'],
['departamento_id' => 12, 'nombre' => 'Larrechea'],
['departamento_id' => 12, 'nombre' => 'Loma Alta'],
['departamento_id' => 12, 'nombre' => 'Lopez'],
['departamento_id' => 12, 'nombre' => 'Maciel'],
['departamento_id' => 12, 'nombre' => 'Monje'],
['departamento_id' => 12, 'nombre' => 'San Eugenio'],
['departamento_id' => 12, 'nombre' => 'San Fabián'],
['departamento_id' => 12, 'nombre' => 'Otro...'],
['departamento_id' => 13, 'nombre' => 'Angeloni'],
['departamento_id' => 13, 'nombre' => 'Cayastacito'],
['departamento_id' => 13, 'nombre' => 'Colonia Dolores'],
['departamento_id' => 13, 'nombre' => 'Colonia Esther'],
['departamento_id' => 13, 'nombre' => 'Colonia Silva'],
['departamento_id' => 13, 'nombre' => 'Gobernador  Crespo'],
['departamento_id' => 13, 'nombre' => 'La Camila'],
['departamento_id' => 13, 'nombre' => 'La Criolla'],
['departamento_id' => 13, 'nombre' => 'La Penca    Y Caraguata'],
['departamento_id' => 13, 'nombre' => 'Marcelino Escalada'],
['departamento_id' => 13, 'nombre' => 'Naré'],
['departamento_id' => 13, 'nombre' => 'Pedro G. Cello'],
['departamento_id' => 13, 'nombre' => 'Ramayon'],
['departamento_id' => 13, 'nombre' => 'San Bernardo'],
['departamento_id' => 13, 'nombre' => 'San Justo'],
['departamento_id' => 13, 'nombre' => 'San Martín Norte'],
['departamento_id' => 13, 'nombre' => 'Vera Y Pintado'],
['departamento_id' => 13, 'nombre' => 'Videla'],
['departamento_id' => 13, 'nombre' => 'Otro...'],
['departamento_id' => 14, 'nombre' => 'Armstrong'],
['departamento_id' => 14, 'nombre' => 'Bouquet'],
['departamento_id' => 14, 'nombre' => 'Las Parejas'],
['departamento_id' => 14, 'nombre' => 'Las Rosas'],
['departamento_id' => 14, 'nombre' => 'Montes De Oca'],
['departamento_id' => 14, 'nombre' => 'Tortugas'],
['departamento_id' => 14, 'nombre' => 'Otro...'],
['departamento_id' => 15, 'nombre' => 'Arequito'],
['departamento_id' => 15, 'nombre' => 'Arteaga'],
['departamento_id' => 15, 'nombre' => 'Bigand'],
['departamento_id' => 15, 'nombre' => 'Casilda'],
['departamento_id' => 15, 'nombre' => 'Chabas'],
['departamento_id' => 15, 'nombre' => 'Los Molinos'],
['departamento_id' => 15, 'nombre' => 'San José De La Esquina'],
['departamento_id' => 15, 'nombre' => 'Sanford'],
['departamento_id' => 15, 'nombre' => 'Villada'],
['departamento_id' => 15, 'nombre' => 'Otro...'],
['departamento_id' => 16, 'nombre' => 'Alcorta'],
['departamento_id' => 16, 'nombre' => 'Cañada Rica'],
['departamento_id' => 16, 'nombre' => 'Cepeda'],
['departamento_id' => 16, 'nombre' => 'Empalme Villa Constitución'],
['departamento_id' => 16, 'nombre' => 'General Gelly'],
['departamento_id' => 16, 'nombre' => 'Godoy'],
['departamento_id' => 16, 'nombre' => 'Juan B. Molina'],
['departamento_id' => 16, 'nombre' => 'Juncal'],
['departamento_id' => 16, 'nombre' => 'La Vanguardia'],
['departamento_id' => 16, 'nombre' => 'Máximo Paz'],
['departamento_id' => 16, 'nombre' => 'Pavón'],
['departamento_id' => 16, 'nombre' => 'Pavón Arriba'],
['departamento_id' => 16, 'nombre' => 'Peyrano'],
['departamento_id' => 16, 'nombre' => 'Rueda'],
['departamento_id' => 16, 'nombre' => 'Santa Teresa'],
['departamento_id' => 16, 'nombre' => 'Sargento Cabral'],
['departamento_id' => 16, 'nombre' => 'Theobald'],
['departamento_id' => 16, 'nombre' => 'Villa Constitución'],
['departamento_id' => 16, 'nombre' => 'Otro...'],
['departamento_id' => 17, 'nombre' => 'Bustinza'],
['departamento_id' => 17, 'nombre' => 'Cañada De Gómez'],
['departamento_id' => 17, 'nombre' => 'Carrizales'],
['departamento_id' => 17, 'nombre' => 'Clason'],
['departamento_id' => 17, 'nombre' => 'Correa'],
['departamento_id' => 17, 'nombre' => 'Lucio V. Lopez'],
['departamento_id' => 17, 'nombre' => 'Oliveros'],
['departamento_id' => 17, 'nombre' => 'Pueblo Andino'],
['departamento_id' => 17, 'nombre' => 'Salto Grande'],
['departamento_id' => 17, 'nombre' => 'Serodino'],
['departamento_id' => 17, 'nombre' => 'Totoras'],
['departamento_id' => 17, 'nombre' => 'Villa Eloisa'],
['departamento_id' => 17, 'nombre' => 'Otro...'],
['departamento_id' => 18, 'nombre' => 'Acebal'],
['departamento_id' => 18, 'nombre' => 'Albarellos'],
['departamento_id' => 18, 'nombre' => 'Alvarez'],
['departamento_id' => 18, 'nombre' => 'Alvear'],
['departamento_id' => 18, 'nombre' => 'Arminda'],
['departamento_id' => 18, 'nombre' => 'Arroyo Seco'],
['departamento_id' => 18, 'nombre' => 'Carmen Del Sauce'],
['departamento_id' => 18, 'nombre' => 'Coronel Bogado'],
['departamento_id' => 18, 'nombre' => 'Coronel Domínguez'],
['departamento_id' => 18, 'nombre' => 'Fighiera'],
['departamento_id' => 18, 'nombre' => 'Funes'],
['departamento_id' => 18, 'nombre' => 'General Lagos'],
['departamento_id' => 18, 'nombre' => 'Granadero Baigorria'],
['departamento_id' => 18, 'nombre' => 'Ibarlucea'],
['departamento_id' => 18, 'nombre' => 'Pérez'],
['departamento_id' => 18, 'nombre' => 'Piñero'],
['departamento_id' => 18, 'nombre' => 'Pueblo Esther'],
['departamento_id' => 18, 'nombre' => 'Pueblo Muñoz'],
['departamento_id' => 18, 'nombre' => 'Rosario'],
['departamento_id' => 18, 'nombre' => 'Soldini'],
['departamento_id' => 18, 'nombre' => 'Uranga'],
['departamento_id' => 18, 'nombre' => 'Villa Amelia'],
['departamento_id' => 18, 'nombre' => 'Villa Gobernador Gálvez'],
['departamento_id' => 18, 'nombre' => 'Zavalla'],
['departamento_id' => 18, 'nombre' => 'Otro...'],
['departamento_id' => 19, 'nombre' => 'Centeno'],
['departamento_id' => 19, 'nombre' => 'San Genaro'],
['departamento_id' => 19, 'nombre' => 'Otro...'],
['departamento_id' => 20, 'nombre' => 'Aldao'],
['departamento_id' => 20, 'nombre' => 'Capitán Bermúdez'],
['departamento_id' => 20, 'nombre' => 'Carcarañá'],
['departamento_id' => 20, 'nombre' => 'Coronel Arnold'],
['departamento_id' => 20, 'nombre' => 'Fray Luis Beltrán'],
['departamento_id' => 20, 'nombre' => 'Fuentes'],
['departamento_id' => 20, 'nombre' => 'Luis Palacios'],
['departamento_id' => 20, 'nombre' => 'Puerto General San Martín'],
['departamento_id' => 20, 'nombre' => 'Pujato'],
['departamento_id' => 20, 'nombre' => 'Ricardone'],
['departamento_id' => 20, 'nombre' => 'Roldán'],
['departamento_id' => 20, 'nombre' => 'San Jeronimo Sud'],
['departamento_id' => 20, 'nombre' => 'San Lorenzo'],
['departamento_id' => 20, 'nombre' => 'Timbúes'],
['departamento_id' => 20, 'nombre' => 'Villa Mugueta'],
['departamento_id' => 20, 'nombre' => 'Otro...'],
['departamento_id' => 21, 'nombre' => 'Cañada Rosquin'],
['departamento_id' => 21, 'nombre' => 'Carlos Pellegrini'],
['departamento_id' => 21, 'nombre' => 'Casas'],
['departamento_id' => 21, 'nombre' => 'Colonia Belgrano'],
['departamento_id' => 21, 'nombre' => 'El Trébol'],
['departamento_id' => 21, 'nombre' => 'Landeta'],
['departamento_id' => 21, 'nombre' => 'Las Bandurrias'],
['departamento_id' => 21, 'nombre' => 'Los Cardos'],
['departamento_id' => 21, 'nombre' => 'Maria Susana'],
['departamento_id' => 21, 'nombre' => 'Piamonte'],
['departamento_id' => 21, 'nombre' => 'Otro...'],
['departamento_id' => 22, 'nombre' => 'Berabevú'],
['departamento_id' => 22, 'nombre' => 'Chañar Ladeado'],
['departamento_id' => 22, 'nombre' => 'Godeken'],
['departamento_id' => 22, 'nombre' => 'Los Quirquinchos'],
['departamento_id' => 22, 'nombre' => 'Otro...'],
['departamento_id' => 23, 'nombre' => 'Bombal'],
['departamento_id' => 23, 'nombre' => 'Otro...'],
['departamento_id' => 24, 'nombre' => 'Aarón Castellanos'],
['departamento_id' => 24, 'nombre' => 'Amenabar'],
['departamento_id' => 24, 'nombre' => 'Cafferata'],
['departamento_id' => 24, 'nombre' => 'Cañada Del Ucle'],
['departamento_id' => 24, 'nombre' => 'Carmen'],
['departamento_id' => 24, 'nombre' => 'Carreras'],
['departamento_id' => 24, 'nombre' => 'Chapuy'],
['departamento_id' => 24, 'nombre' => 'Chovet'],
['departamento_id' => 24, 'nombre' => 'Christophersen'],
['departamento_id' => 24, 'nombre' => 'Diego De Alvear'],
['departamento_id' => 24, 'nombre' => 'Elortondo'],
['departamento_id' => 24, 'nombre' => 'Firmat'],
['departamento_id' => 24, 'nombre' => 'Hughes'],
['departamento_id' => 24, 'nombre' => 'La Chispa'],
['departamento_id' => 24, 'nombre' => 'Labordeboy'],
['departamento_id' => 24, 'nombre' => 'Lazzarino'],
['departamento_id' => 24, 'nombre' => 'Maggiolo'],
['departamento_id' => 24, 'nombre' => 'Maria Teresa'],
['departamento_id' => 24, 'nombre' => 'Melincué'],
['departamento_id' => 24, 'nombre' => 'Miguel Torres'],
['departamento_id' => 24, 'nombre' => 'Murphy'],
['departamento_id' => 24, 'nombre' => 'Rufino'],
['departamento_id' => 24, 'nombre' => 'San Eduardo'],
['departamento_id' => 24, 'nombre' => 'San Francisco De Santa Fe'],
['departamento_id' => 24, 'nombre' => 'San Gregorio'],
['departamento_id' => 24, 'nombre' => 'Sancti Spiritu'],
['departamento_id' => 24, 'nombre' => 'Santa  Isabel'],
['departamento_id' => 24, 'nombre' => 'Teodelina'],
['departamento_id' => 24, 'nombre' => 'Venado Tuerto'],
['departamento_id' => 24, 'nombre' => 'Villa Cañás'],
['departamento_id' => 24, 'nombre' => 'Wheelwright'],
['departamento_id' => 24, 'nombre' => 'Otro...'],
        ]);
        $ajusA = new Ajuste;
        $ajusA->key = 'tos';
        $ajusA->value_type = 'txt';
        $ajusA->value = 'Términos y condiciones de uso.';
        $ajusA->description = 'Términos y condiciones para el uso de la plataforma.';
        $ajusA->save();
        $ajusB = new Ajuste;
        $ajusB->key = 'titulo';
        $ajusB->value_type = 'str';
        $ajusB->value = 'Nueva Ley de Educación Provincial';
        $ajusB->description = 'Título que se muestra en la página de inicio.';
        $ajusB->save();
        $ajusC = new Ajuste;
        $ajusC->key = 'intro';
        $ajusC->value_type = 'txt';
        $ajusC->value = 'Texto de muestra.';
        $ajusC->description = 'Texto introductorio en la página de inicio.';
        $ajusC->save();
        $ajusD = new Ajuste;
        $ajusD->key = 'videos';
        $ajusD->value_type = 'str';
        $ajusD->value = '7uulVAHwXi0';
        $ajusD->description = 'Videos que se muestran en la página de inicio.';
        $ajusD->save();
        $usuario = new Usuario;
        $usuario->email = $_POST['usr_email'];
        $usuario->password = password_hash($_POST['usr_password'], PASSWORD_DEFAULT);
        $usuario->nombre = $_POST['usr_nombre'];
        $usuario->apellido = $_POST['usr_apellido'];
        $usuario->img_tipo = 1;
        $usuario->img_hash = md5(strtolower(trim($usuario->email)));
        $patrulla = new Patrulla;
        $patrulla->nombre = 'Aministrador';
        $patrulla->descripcion = 'Admnistrador que instaló la plataforma.';
        $patrulla->save();
        $poderes = [
            ['nombre' => 'Moderar', 'descripcion' => 'Moderar en la plataforma.'],
            ['nombre' => 'Configurar plataforma', 'descripcion' => 'Configurar parámetros de Virtugora.'],
            ['nombre' => 'Administrar organismos', 'descripcion' => 'Definir los organimos existentes.'],
            ['nombre' => 'Administrar funcionarios', 'descripcion' => 'Asignar los funcionarios a sus respectivos organismos.'],
            ['nombre' => 'Administrar patrullas', 'descripcion' => 'Definir los distintos grupos de moderación.'],
            ['nombre' => 'Administrar moderadores', 'descripcion' => 'Asignar los usuarios que serán moderadores.'],
            ['nombre' => 'Verificar ciudadanos', 'descripcion' => 'Registrar como verificados a usuarios que lo demuestren.'],
        ];
        Poder::insert($poderes);
        $patrulla->poderes()->attach([1,2,3,4,5,6,7]);
        $usuario->patrulla()->associate($patrulla);
        $usuario->save();
    }
} catch (Exception $e) {
    $titulo = '¡Ha ocurrido un error!';
    $descrp =  $e->getMessage();
    $exito = false;
}?>
<h2><strong><?php echo $titulo ?></strong></h2>
<p class="lead"><?php echo $descrp ?></p>
<?php if ($exito) { ?>
        <hr>
        <a class="btn btn-primary btn-block" href='./'>Comenzar</a>
    <?php } ?>
<?php } else { ?>
    <form method="post" class="form-horizontal" action="<?php echo $_SERVER['PHP_SELF']; ?>">
    <h2><strong>¡Bienvenido!</strong></h2>
<p class="lead">Muchas gracias por elegir Virtuágora. Por favor complete los siguientes datos para crear
            la cuenta de administrador principal de la plataforma.</p>
            <hr>
            <div class="form-group">
      <label for="inputEmail" class="col-lg-2 control-label">Email</label>
      <div class="col-lg-10">
        <input type="text" name="usr_email" class="form-control" id="inputEmail" placeholder="miemail@dominio.com">
      </div>
    </div>
    <div class="form-group">
      <label for="inputNombre" class="col-lg-2 control-label">Nombre</label>
      <div class="col-lg-10">
        <input type="text" name="usr_nombre" class="form-control" id="inputNombre" placeholder="Juan">
      </div>
    </div>
    <div class="form-group">
      <label for="inputApellido" class="col-lg-2 control-label">Apellido</label>
      <div class="col-lg-10">
        <input type="text" name="usr_apellido" class="form-control" id="inputApellido" placeholder="Perez">
      </div>
    </div>
    <div class="form-group">
      <label for="inputPassword" class="col-lg-2 control-label">Contraseña</label>
      <div class="col-lg-10">
        <input type="password" name="usr_password" class="form-control" id="inputPassword" placeholder="">
      </div>
    </div>
    <hr>
    <button type="submit" class="btn btn-primary btn-block" name="submit" value="Instalar">Instalar</button>
    </form>
<?php } ?>
</div>
</div>
</div>
</div>
</div>
</body>
</html>
