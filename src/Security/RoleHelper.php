<?php

namespace App\Security;

// Los perfiles que se le pueden asignar a un usuario. Cada uno agrupa los
// permisos finos de role_hierarchy (security.yaml).
final class RoleHelper
{
    public const SUPER_ADMIN = 'ROLE_SUPER_ADMIN';
    public const DIRECTORA = 'ROLE_DIRECTORA';
    public const DOCENTE = 'ROLE_DOCENTE';
    public const FAMILIA = 'ROLE_FAMILIA';

    public const PERFILES = [
        'Directora' => self::DIRECTORA,
        'Docente' => self::DOCENTE,
        'Familia' => self::FAMILIA,
        'Super administrador' => self::SUPER_ADMIN,
    ];

    // Los perfiles del equipo del jardín: los que entran por "Soy del equipo".
    public const PERFILES_EQUIPO = [self::DIRECTORA, self::DOCENTE];

    public static function esDeJardin(string $perfil): bool
    {
        return self::SUPER_ADMIN !== $perfil;
    }

    public static function etiqueta(?string $perfil): string
    {
        return (string) array_search($perfil, self::PERFILES, true);
    }
}
