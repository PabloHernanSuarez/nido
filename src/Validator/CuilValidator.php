<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class CuilValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (empty($value)) {
            return;
        }

        $cuil = str_replace('-', '', $value);

        if (!preg_match('/^\d{11}$/', $cuil)) {
            $this->context->buildViolation($constraint->message)->addViolation();
            return;
        }

        $tipo = (int) substr($cuil, 0, 2);
        
        if (!in_array($tipo, [20, 23, 24, 27])) {
            $this->context->buildViolation($constraint->message)->addViolation();
            return;
        }

        $acumulado = 0;
        $digitos = str_split($cuil);
        $digito = (int) array_pop($digitos);

        for ($i = 0; $i < 10; $i++) {
            $acumulado += $digitos[9 - $i] * (2 + ($i % 6));
        }

        $verif = 11 - ($acumulado % 11);
        $verif = $verif === 11 ? 0 : $verif;

        if ($digito !== $verif) {
            $this->context->buildViolation($constraint->message)->addViolation();
        }
    }
}
