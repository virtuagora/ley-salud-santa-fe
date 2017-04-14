<?php use Augusthur\Validation as Validate;

class EventoCtrl extends Controller {
//    public function ver($idEve) {
//        $vdt = new Validate\QuickValidator([$this, 'notFound']);
//        $vdt->test($idEve, new Validate\Rule\NumNatural());
//        $evento = Evento::with('comentarios')->findOrFail($idEve);
//        $participe = $evento->usuarios()->where('usuario_id', $this->session->user('id'))->first();
//        $participantes = $evento->usuarios()->toArray();
//        $comentarios = $evento->comentarios->toArray();
//        $datosEven = $evento->toArray();
//        $datosEven['interesados'] = $evento->usuarios()->count();
//        $datosPart = $participe? $participe->toArray(): null;
//        $this->render('lpe/contenido/evento/ver.twig', [
//            'evento' => $datosEven,
//            'comentarios' =>  $comentarios,
//            'participacion' => $datosPart,
//            'participantes' => $participantes
//        ]);
//    }
    
//    public function listar() {
//        $eventos = Evento::all()->sortBy('fecha');
//        $this->render('lpe/contenido/evento/listar.twig', [
//            'eventos' => $eventos
//        ]);
//    }

    public function verCrear() {
        $this->render('salud/contenido/evento/crear.twig');
    }

    public function crear() {
        $req = $this->request;
        $vdt = $this->validarEvento($req->post());
        $autor = $this->session->getUser();
        $evento = new Evento;
        $evento->descripcion = $vdt->getData('descripcion');
        $evento->lugar = $vdt->getData('lugar');
        $evento->fecha_desde = Carbon\Carbon::parse($vdt->getData('fecha_desde'));
        $evento->fecha_hasta = Carbon\Carbon::parse($vdt->getData('fecha_hasta'));
        $evento->titulo = $vdt->getData('titulo');
        $evento->info = $vdt->getData('info');
        $evento->autor()->associate($autor);
        $evento->save();
        $this->flash('success', 'Su evento fue creado exitosamente.');
        $this->redirectTo('shwIndexAdmin');
    }

    public function eliminar($idEve) {
        $vdt = new Validate\QuickValidator([$this, 'notFound']);
        $vdt->test($idEve, new Validate\Rule\NumNatural());
        $evento = Evento::findOrFail($idEve);
        $evento->delete();
        $this->flash('success', 'El evento ha sido eliminado exitosamente.');
        $this->redirectTo('shwIndexAdmin');
    }

    private function validarEvento($data) {
        $vdt = new Validate\Validator();
        $vdt->addRule('titulo', new Validate\Rule\MinLength(1))
            ->addRule('lugar', new Validate\Rule\MinLength(1))
            ->addRule('fecha_desde', new Validate\Rule\Date('Y-m-d'))
            ->addRule('fecha_hasta', new Validate\Rule\Date('Y-m-d'))
            ->addRule('descripcion', new Validate\Rule\MinLength(1))
            ->addRule('info', new Validate\Rule\MinLength(1))
            ->addOptional('fecha_hasta')
            ->addOptional('info')
            ->addFilter('fecha_hasta', FilterFactory::emptyToNull())
            ->addFilter('info', FilterFactory::emptyToNull());
        if (!$vdt->validate($data)) {
            throw new TurnbackException($vdt->getErrors());
        }
        return $vdt;
    }
}
