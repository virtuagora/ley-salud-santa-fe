<?php use Augusthur\Validation as Validate;

class DerechoCtrl extends Controller {

    // TODO Matu, pregunta. Los comentarios tienen su karma. Funciona eso?
    // Porque no me aparece a mi.
    public function ver($idDer) {
        $vdt = new Validate\QuickValidator([$this, 'notFound']);
        $vdt->test($idDer, new Validate\Rule\NumNatural());
        $derecho = Derecho::with('contenido')->findOrFail($idDer);
        $contenido = $derecho->contenido;
        $datosDer = array_merge($contenido->toArray(), $derecho->toArray());
        
        $votosUsr = [];
        if ($this->session->user('id')) {
            $votos = $derecho->votos()->where('usuario_id', $this->session->user('id'))->get();
            foreach ($votos as $voto) {
                $votosUsr[$voto->seccion_id] = $voto->postura;
            }
        }
        
        $this->render('salud/contenido/derecho/ver.twig', [
            'derecho' => $datosDer,
            'voto' => $votosUsr
        ]);
    }

    public function verAccion($idDer,$idAcc) {
        $vdt = new Validate\QuickValidator([$this, 'notFound']);
        $vdt->test($idDer, new Validate\Rule\NumNatural());
        $derecho = Derecho::with('contenido')->findOrFail($idDer);
        $contenido = $derecho->contenido;
        $datosDer = array_merge($contenido->toArray(), $derecho->toArray());
        
        $this->render('salud/contenido/derecho/verAccion.twig', [
            'derecho' => $datosDer,
            'seccionMostrar' => $idAcc            
        ]);
    }


    public function verCrear() {
        $this->render('salud/contenido/derecho/crear.twig');
    }
    // TODO Este es el listado que te paso con su nombre
    // titulo - input text
    // orden - number de 1 a 6
    // resumen - textarea
    // cuerpo - textarea
    // secciones, como siempre y como se trabaja aca.. asi que no hay mucho mas.
    public function crear() {
        $req = $this->request;
        $vdt = $this->validarDerecho($req->post());
        $autor = $this->session->getUser();
        $derecho = new Derecho;
        $derecho->descripcion = $vdt->getData('descripcion');
        // TODO fix de estos NN en la bd?
        $derecho->video = 'nada';
        $derecho->imagen = 0;;
        $derecho->save();
        $acciones = $vdt->getData('secciones');
        foreach ($acciones as $accion) {
            $newAccion = new Seccion;
            $newAccion->descripcion = $accion;
            $newAccion->derecho()->associate($derecho);
            $newAccion->save();
        }
        $contenido = new Contenido;
        $contenido->titulo = $vdt->getData('titulo');
        $contenido->resumen = $vdt->getData('resumen');
        $contenido->orden = $vdt->getData('orden');
        $contenido->puntos = 0;
        $contenido->autor()->associate($autor);
        $contenido->contenible()->associate($derecho);
        $contenido->save();
        $this->flash('success', 'El área se creó exitosamente.');
        $this->redirectTo('shwIndexAdmin');
    }
    
    public function verModificar($idDer) {
        $vdt = new Validate\QuickValidator([$this, 'notFound']);
        $vdt->test($idDer, new Validate\Rule\NumNatural());
        //$categorias = Categoria::all()->toArray();
        $derecho = Derecho::with('contenido')->findOrFail($idDer);
        $contenido = $derecho->contenido;
        $datos = array_merge($contenido->toArray(), $derecho->toArray());
        $this->render('salud/contenido/derecho/editar.twig', [
            'derecho' => $datos,
        ]);
    }

    public function modificar($idDer) {
        $vdt = new Validate\QuickValidator([$this, 'notFound']);
        $vdt->test($idDer, new Validate\Rule\NumNatural());
        $derecho = Derecho::with('contenido')->findOrFail($idDer);
        $contenido = $derecho->contenido;
        $usuario = $this->session->getUser();
        $req = $this->request;
        $vdt = $this->validarDerecho($req->post());
        $derecho->descripcion = $vdt->getData('descripcion');
        $derecho->save();
        $contenido->titulo = $vdt->getData('titulo');
        $contenido->resumen = $vdt->getData('resumen');
        $contenido->orden = $vdt->getData('orden');
        $contenido->save();
        $accNew = $vdt->getData('secciones');
        $accOld = $derecho->secciones;
        $i = 0;
        foreach ($accOld as $accion) {
            if (isset($accNew[$i])) {
                $accion->descripcion = $accNew[$i];
                $i++;
                $accion->save();
            }
        }
        $this->flash('success', 'Los datos del área fueron modificados exitosamente.');
        $this->redirectTo('shwIndexAdmin');
    }
    
    public function votar($idSec) {
        $vdt = new Validate\Validator();
        $vdt->addRule('postura', new Validate\Rule\InArray([1,2,3]))
            ->addRule('idSec', new Validate\Rule\NumNatural());
        $req = $this->request;
        $data = array_merge(['idSec' => $idSec], $req->post());
        if (!$vdt->validate($data)) {
            throw new TurnbackException($vdt->getErrors());
        }
        $usuario = $this->session->getUser();
        $seccion = Seccion::with('derecho')->findOrFail($idSec);
        $voto = VotoSeccion::firstOrNew([
            'seccion_id' => $seccion->id,
            'usuario_id' => $usuario->id
        ]);
        $voto->postura = $vdt->getData('postura');
        $voto->save();
        $this->flash('success', 'Su voto fue registrado exitosamente.');
        $this->redirectTo('shwDerecho', ['idDer' => $seccion->derecho->id]);
    }

    private function validarDerecho($data) {
        $vdt = new Validate\Validator();
        $vdt->addRule('titulo', new Validate\Rule\MinLength(1))
            ->addRule('titulo', new Validate\Rule\MaxLength(256))
            ->addRule('descripcion', new Validate\Rule\MinLength(4))
            ->addRule('resumen', new Validate\Rule\MinLength(4))
            ->addRule('orden', new Validate\Rule\NumNatural())
            ->addRule('secciones', new Validate\Rule\MinLength(4))
            ->addRule('secciones', new Validate\Rule\MaxLength(5120))
            ->addFilter('secciones', FilterFactory::explode('&&'));
        if (!$vdt->validate($data)) {
            throw new TurnbackException($vdt->getErrors());
        }
        return $vdt;
    }
    
    private function subirImagen($nombre) {
        $exito = true;
        $dir = __DIR__ . '/../public/img/derecho';
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        $storage = new \Upload\Storage\FileSystem($dir, true);
        $file = new \Upload\File('archivo', $storage);
        $file->setName($nombre);
        $file->addValidations([
            new \Upload\Validation\Mimetype(['image/jpg', 'image/jpeg']),
            new \Upload\Validation\Size('2M')
        ]);
        try {
            $file->upload();
        } catch (\Exception $e) {
            $exito = false;
        }
        return $exito;
    }
}
