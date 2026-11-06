<?php

namespace App\Validator;

use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class NotCurrentPasswordValidator extends ConstraintValidator
{
    public function __construct(
        private Security $security,
        private UserPasswordHasherInterface $passwordHasher,
    ) {}

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (empty($value)) {
            return;
        }

        $user = $this->security->getUser();

        if ($user === null) {
            return;
        }
        
        if ($this->passwordHasher->isPasswordValid($user, $value)) {
            $this->context->buildViolation($constraint->message)->addViolation();
        }
    }
}
