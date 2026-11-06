<?php

namespace App\Security;

use App\Entity\Usuario;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user): void
    {
        if (!$user instanceof Usuario) {
            return;
        }

        // Un jardín dado de baja deja afuera a todo su equipo y sus familias.
        if (!$user->isActive() || (null !== $user->getJardin() && !$user->getJardin()->isActivo())) {
            throw new CustomUserMessageAccountStatusException(
                'No podés entrar al sistema. Por favor comunicate con el jardín.'
            );
        }
    }

    public function checkPostAuth(UserInterface $user): void {}
}
