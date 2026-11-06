<?php

namespace App\Monolog;

use Monolog\Handler\FingersCrossed\ActivationStrategyInterface;
use Monolog\Level;
use Monolog\LogRecord;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

// Decide si un error se manda por email (ver when@prod en monolog.yaml). Los
// bots que escanean el sitio generan infinitos 404 anónimos, así que sólo se
// avisa de los errores de usuarios logueados y de las pantallas de login y
// reseteo de contraseña (que por definición son anónimas). De la consola sólo
// avisa de lo que corre el cron: los comandos ejecutados a mano en el servidor
// (d:s:u, cache:clear...) ya muestran el error en pantalla.
//
// En login y reseteo de contraseña tampoco se avisa de los errores 4xx: un
// usuario real no los genera (una contraseña o un CSRF inválidos son fallos de
// autenticación, no excepciones), son bots que postean el formulario a mano.
class ErrorEmailActivationStrategy implements ActivationStrategyInterface
{
    private const RUTAS_ANONIMAS = '#^/(login|reset-password)(/|$)#';

    // Clave en la que el firewall "main" guarda el token del usuario logueado.
    private const CLAVE_TOKEN_SESION = '_security_main';

    public function __construct(
        private readonly RequestStack $requestStack,
    ) {
    }

    public function isHandlerActivated(LogRecord $record): bool
    {
        if ($record->level->isLowerThan(Level::Error)) {
            return false;
        }

        $request = $this->requestStack->getMainRequest();
        // Sin request es un comando de consola: el cron corre sin terminal, uno
        // ejecutado a mano tiene la terminal en STDIN.
        if (null === $request) {
            return !\defined('STDIN') || !stream_isatty(\STDIN);
        }

        if (preg_match(self::RUTAS_ANONIMAS, $request->getPathInfo())) {
            $exception = $record->context['exception'] ?? null;

            return !$exception instanceof HttpExceptionInterface || $exception->getStatusCode() >= 500;
        }

        // Se mira la sesión y no el token del firewall porque un 404 de ruta
        // inexistente lo lanza el router antes de que corra el firewall.
        try {
            return $request->hasPreviousSession()
                && $request->getSession()->has(self::CLAVE_TOKEN_SESION);
        }
        catch (\Throwable) {
            // Leer la sesión puede fallar (p. ej. si ya se enviaron los headers
            // y no se puede abrir): ante la duda, se manda el email.
            return true;
        }
    }
}
