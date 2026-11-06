<?php

namespace App\Form\Type;

use App\Entity\Jardin;
use App\Entity\Usuario;
use App\Security\RoleHelper;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UsuarioType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // La directora sólo da de alta gente de su jardín, y nunca un super
        // administrador: el jardín no se elige y el perfil se acota.
        $perfiles = RoleHelper::PERFILES;
        if (null !== $options['jardin']) {
            unset($perfiles[array_search(RoleHelper::SUPER_ADMIN, $perfiles, true)]);
        }

        $builder
            ->add('nombre', TextType::class)
            ->add('apellido', TextType::class)
            ->add('email', EmailType::class, [
                'label' => 'Mail',
                'help' => 'Con este mail entra al sistema y recupera la contraseña.',
                'attr' => ['autocomplete' => 'off'],
            ])
            ->add('perfil', ChoiceType::class, [
                'choices' => $perfiles,
                'placeholder' => 'Seleccioná un perfil',
            ])
        ;

        if (null === $options['jardin']) {
            $builder->add('jardin', EntityType::class, [
                'class' => Jardin::class,
                'label' => 'Jardín',
                'required' => false,
                'placeholder' => 'Ninguno (super administrador)',
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Usuario::class,
            'jardin' => null,
        ]);
        $resolver->setAllowedTypes('jardin', ['null', Jardin::class]);
    }
}
