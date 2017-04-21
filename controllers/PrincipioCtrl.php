<?php use Augusthur\Validation as Validate;

class PrincipioCtrl extends Controller {

    public function verCrear() {
        $this->render('salud/contenido/principio/crear.twig');
    }

    public function eliminar($idPri) {
        $vdt = new Validate\QuickValidator([$this, 'notFound']);
        $vdt->test($idPri, new Validate\Rule\NumNatural());
        $testimonio = Principio::findOrFail($idPri);
        $testimonio->delete();
        $this->flash('success', 'El principio ha sido eliminado exitosamente.');
        $this->redirectTo('shwIndexAdmin');
    }

    public function crear() {
        $req = $this->request;
        $vdt = $this->validarPrincipio($req->post());
        $testimonio = new Principio;
        $testimonio->descripcion = $vdt->getData('descripcion');
        $testimonio->orden = $vdt->getData('orden');
        $testimonio->titulo = $vdt->getData('titulo');
        $testimonio->save();
        $this->flash('success', 'El principio se creó exitosamente.');
        $this->redirectTo('shwIndexAdmin');
    }

    private function validarPrincipio($data) {
        $vdt = new Validate\Validator();
        $vdt->addRule('descripcion', new Validate\Rule\MinLength(2))
            ->addRule('titulo', new Validate\Rule\MinLength(2))
            ->addRule('orden', new Validate\Rule\NumNatural());
        if (!$vdt->validate($data)) {
            throw new TurnbackException($vdt->getErrors());
        }
        return $vdt;
    }
}
