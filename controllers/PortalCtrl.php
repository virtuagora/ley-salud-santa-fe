<?php use Augusthur\Validation as Validate;

class PortalCtrl extends Controller {

    public function verIndex() {
        $derechos = Contenido::where('contenible_type', 'Derecho')->get()->toArray();
        $testimonios = Testimonio::all()->toArray();
        $this->render('salud/portal/inicio.twig',  [
            'derechos' => $derechos,
            'testimonios' => $testimonios,
        ]);
    }

    public function verPortal() {
        $this->render('salud/portal/inicio.twig');
    }

    public function verLogin() {
        $this->render('salud/registro/login-static.twig');
    }

     public function verAcerca() {
        $this->render('salud/portal/acerca.twig');
    }

    // TODO Controlame si esta bien.. lo saque de listar eventos.
    // Esto se mostraria en ver actividades.. esta bien, no?
    public function verActividades() {
        $eventos = Evento::all()->sortBy('fecha');
        $this->render('salud/portal/actividades.twig', [
            'actividades' => $eventos
        ]);
    }
    public function verAreas() {
        $derechos = Contenido::with('Contenible')->where('contenible_type', 'Derecho')->get()->toArray();
        $this->render('salud/portal/areas.twig',  [
            'derechos' => $derechos
        ]   );
    }

    // No lo usamos
    public function verTos() {
        $this->render('salud/portal/tos.twig');
    }

    public function login() {
        $vdt = new Validate\Validator();
        $vdt->addRule('email', new Validate\Rule\Email())
            ->addRule('email', new Validate\Rule\MaxLength(128))
            ->addRule('password', new Validate\Rule\MaxLength(128));
        $req = $this->request;
        if ($vdt->validate($req->post()) && $this->session->login($vdt->getData('email'), $vdt->getData('password'))) {
            $this->redirectTo('shwIndex');
        } else {
            $this->flash('errors', array('Datos de ingreso incorrectos. Por favor vuelva a intentarlo.'));
            $this->redirectTo('shwLogin');
        }
    }

    public function logout() {
        $this->session->logout();
        $this->redirectTo('shwIndex');
    }

    public function verRegistrar() {
        $departamentos = Departamento::with('localidades')->get()->sortBy('nombre')->toArray();
        $ocupaciones = ['Estudiante','Docente','Asistente escolar','Representante gremial',
            'Profesional','Empleado/a en relación de dependencia','Comerciante',
            'Funcionario/a, legislador/a o autoridad gubernamental','Representante de organización social',
            'Trabajador/a doméstico/a no remunerado/a'];
        $this->render('salud/registro/registro.twig', [
            'departamentos' => $departamentos,
            'ocupaciones' => $ocupaciones,
        ]);
    }

    public function registrar() {
        $vdt = new Validate\Validator();
        $vdt->addRule('nombre', new Validate\Rule\Alpha(array(' ')))
            ->addRule('nombre', new Validate\Rule\MinLength(1))
            ->addRule('nombre', new Validate\Rule\MaxLength(32))
            ->addRule('apellido', new Validate\Rule\Alpha(array(' ')))
            ->addRule('apellido', new Validate\Rule\MinLength(1))
            ->addRule('apellido', new Validate\Rule\MaxLength(32))
            ->addRule('password', new Validate\Rule\MinLength(8))
            ->addRule('password', new Validate\Rule\MaxLength(128))
            ->addRule('password', new Validate\Rule\Matches('password2'))
            ->addRule('email', new Validate\Rule\Email())
            ->addRule('email', new Validate\Rule\MaxLength(128))
            ->addRule('email', new Validate\Rule\Unique('usuarios'))
            ->addRule('genero', new Validate\Rule\InArray(['f', 'm']))
            ->addRule('nacimiento', new Validate\Rule\Date('Y-m-d'))
            ->addRule('ocupacion', new Validate\Rule\MaxLength(128))
            ->addRule('extra', new Validate\Rule\MaxLength(128))
            ->addRule('institucion', new Validate\Rule\MaxLength(128))
            ->addRule('localidad', new Validate\Rule\NumNatural())
            ->addRule('localidad', new Validate\Rule\NumMin(1))
            ->addRule('localidad', new Validate\Rule\NumMax(363))
            ->addFilter('email', 'strtolower')
            ->addFilter('email', 'trim');
        $req = $this->request;
        if (!$vdt->validate($req->post())) {
            throw new TurnbackException($vdt->getErrors());
        }
        $recaptcha = new \ReCaptcha\ReCaptcha('6LcF-CYTAAAAACfCi_a60IK5E57PGC0xDp4Ko5ex');
        $resp = $recaptcha->verify($vdt->getData('g-recaptcha-response'));
        if (!$resp->isSuccess()) {
            throw new TurnbackException('El CAPTCHA es inválido.');
        }
        $cumple = Carbon\Carbon::parse($vdt->getData('nacimiento'));
        $limInf = Carbon\Carbon::create(1900, 1, 1, 0, 0, 0);
        $limSup = Carbon\Carbon::now();
        if (!$cumple->between($limInf, $limSup)) {
            throw new TurnbackException('Fecha de nacimiento inválida.');
        }
        $preuser = Preusuario::firstOrNew(['email' => $vdt->getData('email')]);
        $preuser->password = password_hash($vdt->getData('password'), PASSWORD_DEFAULT);
        $preuser->nombre = $vdt->getData('nombre');
        $preuser->apellido = $vdt->getData('apellido');
        $preuser->genero = $vdt->getData('genero');
        $preuser->nacimiento = $cumple;
        $preuser->ocupacion = $vdt->getData('ocupacion');
        $preuser->extra = $vdt->getData('extra');
        $preuser->institucion = $vdt->getData('institucion');
        $preuser->localidad_id = $vdt->getData('localidad');
        $preuser->emailed_token = bin2hex(openssl_random_pseudo_bytes(16));
        $preuser->save();
        
        $to = $preuser->email;
        $subject = 'Confirma tu registro - Santa Fe es Salud';
        $message = $this->view->fetch('email/registro.twig', [
            'id' => $preuser->id,
            'token' => $preuser->emailed_token
        ]);
        mail($to, $subject, $message);
        
        $this->render('salud/registro/registro-exito.twig', array('email' => $preuser->email));
    }

    public function verificarEmail($idPre, $token) {
        $vdt = new Validate\QuickValidator(array($this, 'notFound'));
        $vdt->test($idPre, new Validate\Rule\NumNatural());
        $vdt->test($token, new Validate\Rule\AlphaNumeric());
        $vdt->test($token, new Validate\Rule\ExactLength(32));
        $preuser = Preusuario::findOrFail($idPre);
        if ($token == $preuser->emailed_token) {
            $usuario = new Usuario;
            $usuario->email = $preuser->email;
            $usuario->password = $preuser->password;
            $usuario->nombre = $preuser->nombre;
            $usuario->apellido = $preuser->apellido;
            $usuario->genero = $preuser->genero;
            $usuario->nacimiento = $preuser->nacimiento;
            $usuario->ocupacion = $preuser->ocupacion;
            $usuario->extra = $preuser->extra;
            $usuario->institucion = $preuser->institucion;
            $usuario->localidad_id = $preuser->localidad_id;
            $usuario->puntos = 0;
            $usuario->suspendido = false;
            $usuario->es_funcionario = false;
            $usuario->es_jefe = false;
            $usuario->img_tipo = 1;
            $usuario->img_hash = md5($preuser->email);
            $usuario->save();
            $preuser->delete();
            $this->render('salud/registro/validar-correo.twig', array('usuarioValido' => true,
                                                                'email' => $usuario->email));
        } else {
            $this->render('salud/registro/validar-correo.twig', array('usuarioValido' => false));
        }
    }

    public function verRecuperarClave() {
        $this->render('salud/registro/recuperar-clave.twig');
    }
    
    public function recuperarClave() {
        $vdt = new Validate\Validator();
        $vdt->addRule('email', new Validate\Rule\Email())
            ->addRule('email', new Validate\Rule\MaxLength(128))
            ->addFilter('email', 'strtolower')
            ->addFilter('email', 'trim');
        $req = $this->request;
        if (!$vdt->validate($req->post())) {
            throw new TurnbackException($vdt->getErrors());
        }
        $usuario = Usuario::where('email', $vdt->getData('email'))->first();
        if (is_null($usuario)) {
            throw new TurnbackException('Email inválido. ¿Estás seguro de que te registraste?');
        }
        $usuario->token = bin2hex(openssl_random_pseudo_bytes(16));
        $usuario->save();
        
        $to = $usuario->email;
        $subject = 'Reiniciar clave - A Toda salud - Santa Fe';
        $message = $this->view->fetch('email/recuperar.twig', [
            'id' => $usuario->id,
            'token' => $usuario->token
        ]);
        mail($to, $subject, $message);
        
        $this->redirectTo('shwRecuperarClave');
    }
    
    public function verReiniciarClave($idUsu, $token) {
        $vdt = new Validate\QuickValidator(array($this, 'notFound'));
        $vdt->test($idUsu, new Validate\Rule\NumNatural());
        $vdt->test($token, new Validate\Rule\AlphaNumeric());
        $vdt->test($token, new Validate\Rule\ExactLength(32));
        $this->render('salud/registro/reiniciar-clave.twig', ['idUsu' => $idUsu, 'token' => $token]);
    }
    
    public function reiniciarClave($idUsu, $token) {
        $vdt = new Validate\QuickValidator(array($this, 'notFound'));
        $vdt->test($idUsu, new Validate\Rule\NumNatural());
        $vdt->test($token, new Validate\Rule\AlphaNumeric());
        $vdt->test($token, new Validate\Rule\ExactLength(32));
        $vdt = new Validate\Validator();
        $vdt->addRule('password', new Validate\Rule\MinLength(8))
            ->addRule('password', new Validate\Rule\MaxLength(128))
            ->addRule('password', new Validate\Rule\Matches('password2'));
        if (!$vdt->validate($this->request->post())) {
            throw new TurnbackException($vdt->getErrors());
        }
        $usuario = Usuario::findOrFail($idUsu);
        if ($token != $usuario->token) {
            throw new TurnbackException('El link ha expirado o es inválido. Recordá que solamente es válido por una hora.');
        }
        $ahora = Carbon\Carbon::now();
        if ($ahora->gt($usuario->updated_at->addHour())) {
            throw new TurnbackException('El link ha expirado o es inválido. Recordá que solamente es válido por una hora.');
        }
        $usuario->token = null;
        $usuario->password = password_hash($vdt->getData('password'), PASSWORD_DEFAULT);
        $usuario->save();
        $this->redirectTo('endReiniciarClave');
    }
    
    public function finReiniciarClave() {
        $this->render('salud/registro/reiniciar-completo.twig');
    }
}
