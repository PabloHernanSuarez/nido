<?php

namespace App\EventSubscriber;

use App\Entity\Usuario;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\FlashBagAwareSessionInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;

/**
 * Cierra la sesión que quedó abierta (el celular olvidado en el mostrador) a
 * los app.sesion.minutos_inactividad sin uso. Mientras se la usa no vence. Se
 * controla acá y no con el vencimiento de PHP porque ése depende de una
 * limpieza que no corre en un momento exacto, y la cookie dura hasta que se
 * cierra el navegador, que en un celular casi nunca pasa.
 */
class SesionVencidaSubscriber implements EventSubscriberInterface
{
    private const ULTIMO_USO = 'sesion_ultimo_uso';

    public function __construct(
        private Security $security,
        private RouterInterface $router,
        #[Autowire('%app.sesion.minutos_inactividad%')] private int $minutosInactividad,
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            LoginSuccessEvent::class => 'onLoginSuccess',
            // Después del firewall (8), que carga el usuario. Comparte prioridad
            // con UsuarioDeshabilitadoSubscriber: los dos cierran la sesión, así
            // que da igual cuál corre primero.
            KernelEvents::REQUEST => ['onKernelRequest', 7],
        ];
    }

    // El login conserva los datos de la sesión anterior (sólo cambia el id), así
    // que el tiempo se reinicia a mano.
    public function onLoginSuccess(LoginSuccessEvent $event): void
    {
        $event->getRequest()->getSession()->set(self::ULTIMO_USO, time());
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        if (!$this->security->getUser() instanceof Usuario) {
            return;
        }

        $request = $event->getRequest();
        $session = $request->getSession();
        $ahora = time();

        // Las sesiones abiertas antes de que existiera este control no tienen
        // el tiempo guardado: empiezan a contar desde ahora.
        $ultimoUso = $session->get(self::ULTIMO_USO, $ahora);

        if ($ahora - $ultimoUso <= $this->minutosInactividad * 60) {
            $session->set(self::ULTIMO_USO, $ahora);

            return;
        }

        $response = $this->security->logout(false)
            ?? new RedirectResponse($this->router->generate('app_login'));

        // logout() invalida la sesión: el mensaje se guarda después, en la
        // sesión nueva.
        if ($session instanceof FlashBagAwareSessionInterface) {
            $session->getFlashBag()->add(
                'warning',
                'Su sesión se cerró por inactividad. Vuelva a ingresar para continuar.'
            );
        }

        $event->setResponse($response);
    }
}
