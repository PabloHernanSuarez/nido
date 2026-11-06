<?php

namespace App\EventSubscriber;

use App\Entity\Usuario;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\FlashBagAwareSessionInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RouterInterface;

/**
 * Cierra la sesión de un usuario deshabilitado en su próximo request. UserChecker
 * solo lo controla al iniciar sesión, y Symfony, al recargar el usuario en cada
 * request, compara contraseña, roles y nombre de usuario pero no isActive: sin esto,
 * un usuario deshabilitado seguía trabajando hasta que cerraba sesión.
 * Durante un switch_user se controla al usuario impersonado: si está deshabilitado,
 * nadie debería estar usando el sistema como él.
 */
class UsuarioDeshabilitadoSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private Security $security,
        private RouterInterface $router,
    ) {}

    public static function getSubscribedEvents(): array
    {
        // Después del firewall (8), que carga el usuario desde la base, y antes
        // que PasswordChangeSubscriber (6).
        return [KernelEvents::REQUEST => ['onKernelRequest', 7]];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $user = $this->security->getUser();

        if (!$user instanceof Usuario || $user->isActive()) {
            return;
        }

        $response = $this->security->logout(false)
            ?? new RedirectResponse($this->router->generate('app_login'));

        // logout() invalida la sesión: el mensaje se agrega después, en la sesión nueva.
        $session = $event->getRequest()->getSession();
        if ($session instanceof FlashBagAwareSessionInterface) {
            $session->getFlashBag()->add(
                'danger',
                'No puede acceder al sistema. Por favor contacte al administrador.'
            );
        }

        $event->setResponse($response);
    }
}
