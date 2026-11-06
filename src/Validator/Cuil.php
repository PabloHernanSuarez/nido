<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class Cuil extends Constraint
{
    public string $message = 'El CUIL ingresado no es válido.';
}
