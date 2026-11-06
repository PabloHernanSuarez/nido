<?php

namespace App\Security;

use App\Entity\Jardin;
use App\Entity\Usuario;
use Symfony\Bundle\SecurityBundle\Security;

// El jardín sobre el que trabaja el usuario logueado: el suyo. Todo listado o
// alta de un dato de un jardín pasa por acá, para que la información de un
// jardín nunca se mezcle con la de otro. El super administrador no tiene
// jardín y ve todos.
class JardinActualResolver
{
    public function __construct(
        private readonly Security $security,
    ) {
    }

    public function getJardin(): ?Jardin
    {
        $usuario = $this->security->getUser();

        return $usuario instanceof Usuario ? $usuario->getJardin() : null;
    }

    // Si el dato es de otro jardín, para el usuario logueado no existe.
    public function puedeVer(?Jardin $jardin): bool
    {
        $actual = $this->getJardin();

        return null === $actual || $actual === $jardin;
    }
}
