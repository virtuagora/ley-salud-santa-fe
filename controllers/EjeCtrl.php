<?php use Augusthur\Validation as Validate;

class EjeCtrl extends Controller {

    public function ver($idEje) {
        $vdt = new Validate\QuickValidator([$this, 'notFound']);
        $vdt->test($idEje, new Validate\Rule\NumNatural());
        $eje = Eje::with('contenido')->findOrFail($idEje);
        $contenido = $eje->contenido;
        $datosEje = array_merge($contenido->toArray(), $eje->toArray());
        $rta = $this->session->user('id')?
            $eje->respuestas()->where('usuario_id', $this->session->user('id'))->first():
            null;

        $listaEjes = Ajuste::where('key', 'ejes')->first();
        $listaEjes = is_null($listaEjes)? [$idEje]: explode(',', $listaEjes->value);

        $pos = array_search($idEje, $listaEjes);
        $idNext = isset($listaEjes[$pos+1])? $listaEjes[$pos+1]: $listaEjes[0];
        $next = Eje::with('contenido')->find($idNext)->contenido->toArray();
        $idPrev = isset($listaEjes[$pos-1])? $listaEjes[$pos-1]: end($listaEjes);
        $prev = Eje::with('contenido')->find($idPrev)->contenido->toArray();

        $this->render('salud/contenido/eje/ver.twig', [
            'eje' => $datosEje,
            'respuesta' => $rta,
            'anterior' => $prev,
            'siguiente' => $next,
        ]);
    }

    //TODO esto que onda?
//    public function verAccion($idDer,$idAcc) {
//        $vdt = new Validate\QuickValidator([$this, 'notFound']);
//        $vdt->test($idDer, new Validate\Rule\NumNatural());
//        $derecho = Derecho::with('contenido')->findOrFail($idDer);
//        $contenido = $derecho->contenido;
//        $datosDer = array_merge($contenido->toArray(), $derecho->toArray());
//
//        $this->render('salud/contenido/derecho/verAccion.twig', [
//            'derecho' => $datosDer,
//            'seccionMostrar' => $idAcc
//        ]);
//    }


    public function verCrear() {
        $this->render('salud/contenido/eje/crear.twig');
    }
    
    public function crear() {
        $req = $this->request;
        $vdt = $this->validarEje($req->post());
        $autor = $this->session->getUser();
        $eje = new Eje;
        $eje->descripcion = $vdt->getData('descripcion');
        $eje->links = $vdt->getData('links');
        $eje->preguntas = $vdt->getData('preguntas');
        $eje->save();
        $contenido = new Contenido;
        $contenido->titulo = $vdt->getData('titulo');
        $contenido->resumen = $vdt->getData('resumen');
        $contenido->orden = $vdt->getData('orden');
        $contenido->puntos = 0;
        $contenido->autor()->associate($autor);
        $contenido->contenible()->associate($eje);
        $contenido->save();
        $listaEjes = Ajuste::where('key', 'ejes')->first();
        $listaEjes->value = empty($listaEjes->value)? $eje->id: $listaEjes->value.','.$eje->id;
        $listaEjes->save();
        $this->flash('success', 'El eje se creó exitosamente.');
        $this->redirectTo('shwIndexAdmin');
    }
    
    public function verModificar($idEje) {
        $vdt = new Validate\QuickValidator([$this, 'notFound']);
        $vdt->test($idEje, new Validate\Rule\NumNatural());
        $eje = Eje::with('contenido')->findOrFail($idEje);
        $contenido = $eje->contenido;
        $datos = array_merge($contenido->toArray(), $eje->toArray());
        $this->render('salud/contenido/eje/editar.twig', [
            'eje' => $datos,
        ]);
    }

    public function modificar($idEje) {
        $vdt = new Validate\QuickValidator([$this, 'notFound']);
        $vdt->test($idEje, new Validate\Rule\NumNatural());
        $eje = Eje::with('contenido')->findOrFail($idEje);
        $contenido = $eje->contenido;
        $usuario = $this->session->getUser();
        $req = $this->request;
        $vdt = $this->validarEje($req->post());
        $eje->descripcion = $vdt->getData('descripcion');
        $eje->links = $vdt->getData('links');
//        $eje->preguntas = $vdt->getData('preguntas');
        $eje->save();
        $contenido->titulo = $vdt->getData('titulo');
        $contenido->resumen = $vdt->getData('resumen');
        $contenido->orden = $vdt->getData('orden');
        $contenido->save();
        $this->flash('success', 'Los datos del eje fueron modificados exitosamente.');
        $this->redirectTo('shwIndexAdmin');
    }
    
    public function responder($idEje) {
        $vdt = new Validate\Validator();
        $vdt->addRule('valoracion', new Validate\Rule\InArray([0,1,2,3]))
            ->addRule('idEje', new Validate\Rule\NumNatural())
            ->addRule('respuestas', new Validate\Rule\MaxLength(5120))
            ->addFilter('respuestas', FilterFactory::json_decode());
        $req = $this->request;
        $data = array_merge(['idEje' => $idEje], $req->post());
        if (!$vdt->validate($data)) {
            throw new TurnbackException($vdt->getErrors());
        }
        $usuario = $this->session->getUser();
        $eje = Eje::findOrFail($idEje);
        $rta = Respuesta::firstOrNew([
            'eje_id' => $eje->id,
            'usuario_id' => $usuario->id
        ]);
        $rta->valoracion = $vdt->getData('valoracion');
        $rta->respuestas = $vdt->getData('respuestas');
        $rta->save();
        $contenido = $eje->contenido;
        $contenido->puntos = $eje->respuestas()->sum('valoracion');
        $contenido->save();
        $this->flash('success', 'Su respuesta fue registrada exitosamente.');
        $this->redirectTo('shwEje', ['idEje' => $eje->id . '#exito']);
    }

    private function validarEje($data) {
        $vdt = new Validate\Validator();
        $vdt->addRule('titulo', new Validate\Rule\MinLength(1))
            ->addRule('titulo', new Validate\Rule\MaxLength(256))
            ->addRule('descripcion', new Validate\Rule\MinLength(4))
            ->addRule('resumen', new Validate\Rule\MinLength(4))
            ->addRule('orden', new Validate\Rule\NumNatural())
            ->addRule('preguntas', new Validate\Rule\MinLength(4))
            ->addRule('preguntas', new Validate\Rule\MaxLength(5120))
            ->addFilter('preguntas', FilterFactory::explode('&&'))
            ->addRule('links', new Validate\Rule\Attributes([
                'url' => FilterFactory::validateUrl(),
                'label' => FilterFactory::validateLength(2,512),
            ]))
            ->addFilter('links', FilterFactory::json_decode());
        if (!$vdt->validate($data)) {
            throw new TurnbackException($vdt->getErrors());
        }
        return $vdt;
    }
    
//    private function subirImagen($nombre) {
//        $exito = true;
//        $dir = __DIR__ . '/../public/img/derecho';
//        if (!is_dir($dir)) {
//            mkdir($dir, 0777, true);
//        }
//        $storage = new \Upload\Storage\FileSystem($dir, true);
//        $file = new \Upload\File('archivo', $storage);
//        $file->setName($nombre);
//        $file->addValidations([
//            new \Upload\Validation\Mimetype(['image/jpg', 'image/jpeg']),
//            new \Upload\Validation\Size('2M')
//        ]);
//        try {
//            $file->upload();
//        } catch (\Exception $e) {
//            $exito = false;
//        }
//        return $exito;
//    }
}
