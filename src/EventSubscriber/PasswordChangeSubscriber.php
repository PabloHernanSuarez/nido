<?php

namespace App\EventSubscriber;

use App\Entity\Usuario;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\SwitchUserToken;

class PasswordChangeSubscriber implements EventSubscriberInterface
{
    private const RUTAS_EXCLUIDAS = ['app_usuario_cambiar_password', 'app_logout'];

    public function __construct(
        private TokenStorageInterface $tokenStorage,
        private RouterInterface $router,
    ) {}

    public static function getSubscribedEvents(): array
    {
        // Después de UsuarioDeshabilitadoSubscriber (7): a un usuario deshabilitado
        // se le cierra la sesión antes de pedirle que cambie la contraseña.
        return [KernelEvents::REQUEST => ['onKernelRequest', 6]];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $token = $this->tokenStorage->getToken();
        
        // El cambio obligatorio es para el dueño de la cuenta: quien la usa con
        // switch_user no tiene que cambiarle la contraseña para poder entrar.
        if (!$token || $token instanceof SwitchUserToken) {
            return;
        }

        $user = $token->getUser();
        
        if (!$user instanceof Usuario || $user->getPasswordChangedAt() !== null) {
            return;
        }

        $route = $event->getRequest()->attributes->get('_route');
        
        if (in_array($route, self::RUTAS_EXCLUIDAS, true)) {
            return;
        }

        $event->setResponse(new RedirectResponse($this->router->generate('app_usuario_cambiar_password')));
    }
}
