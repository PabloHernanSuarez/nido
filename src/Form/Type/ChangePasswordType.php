<?php

namespace App\Form\Type;

use App\Validator\NotCurrentPassword;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\Sequentially;

class ChangePasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // Se pide al cambiar la contraseña con la sesión iniciada, para que una
        // sesión abierta no alcance para quedarse con la cuenta. En el
        // restablecimiento por mail no: el usuario no la recuerda.
        if ($options['pedir_password_actual']) {
            $builder->add('currentPassword', PasswordType::class, [
                'mapped' => false,
                'label' => 'Contraseña actual',
                // En secuencia, para que con el campo vacío no avise además
                // que la contraseña es incorrecta.
                'constraints' => [
                    new Sequentially([
                        new NotBlank([
                            'message' => 'Ingresá tu contraseña actual.',
                        ]),
                        new UserPassword([
                            'message' => 'La contraseña actual no es correcta.',
                        ]),
                    ]),
                ],
                'attr' => [
                    'autocomplete' => 'current-password',
                ],
            ]);
        }

        $builder
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'first_options' => [
                    'error_bubbling' => false,
                    'constraints' => [
                        new NotBlank([
                            'message' => 'Ingresá una contraseña. Tiene que tener por lo menos 8 caracteres, al menos una mayúscula, una minúscula y un número.',
                        ]),
                        new Regex([
                            'pattern' => '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,64}$/',
                            'message' => 'La contraseña tiene que tener por lo menos 8 caracteres, al menos una mayúscula, una minúscula y un número.',
                        ]),
                        new NotCurrentPassword(),
                    ],
                    'label' => 'Nueva contraseña',
                    'attr' => [
                        'placeholder' => 'Mín. 8 caracteres, 1 mayúscula, 1 minúscula, 1 nro.',
                        'autocomplete' => 'new-password',
                    ],
                ],
                'second_options' => [
                    'error_bubbling' => false,
                    'constraints' => [
                        new NotBlank([
                            'message' => 'Repetí la contraseña.',
                        ]),
                    ],
                    'label' => 'Repetí la contraseña',
                    'attr' => [
                        'onpaste' => 'return false',
                        'autocomplete' => 'new-password',
                    ],
                ],
                'invalid_message' => 'Las contraseñas no coinciden.',
                // Instead of being set onto the object directly,
                // this is read and encoded in the controller
                'mapped' => false,
                'label' => false
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'pedir_password_actual' => false,
        ]);
        $resolver->setAllowedTypes('pedir_password_actual', 'bool');
    }
}
