<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class NotCurrentPassword extends Constraint
{
    public string $message = 'La nueva contraseña no puede ser igual a la actual.';
}
