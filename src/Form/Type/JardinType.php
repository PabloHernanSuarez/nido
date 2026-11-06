<?php

namespace App\Form\Type;

use App\Entity\Jardin;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ColorType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Image;

class JardinType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nombre', TextType::class, [
                'attr' => ['placeholder' => 'Ej.: Jardín Los Girasoles'],
            ])
            ->add('direccion', TextType::class, [
                'label' => 'Dirección',
                'required' => false,
            ])
            ->add('telefono', TextType::class, [
                'label' => 'Teléfono',
                'required' => false,
            ])
            ->add('color', ColorType::class, [
                'label' => 'Color del jardín',
                'help' => 'Se usa en la barra y los botones de su espacio.',
            ])
            // El archivo lo guarda el controlador; la entidad sólo tiene el nombre.
            ->add('archivoLogo', FileType::class, [
                'label' => 'Logo',
                'mapped' => false,
                'required' => false,
                'help' => 'PNG, JPG, WEBP o SVG de hasta 1 MB.',
                'constraints' => [
                    new Image([
                        'maxSize' => '1M',
                        'mimeTypes' => ['image/png', 'image/jpeg', 'image/webp', 'image/svg+xml'],
                        'mimeTypesMessage' => 'El logo tiene que ser PNG, JPG, WEBP o SVG.',
                        'maxSizeMessage' => 'El logo no puede pesar más de 1 MB.',
                    ]),
                ],
            ])
            ->add('activo', CheckboxType::class, [
                'label' => 'Activo',
                'required' => false,
                'help' => 'Si no está activo, nadie del jardín puede entrar.',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Jardin::class,
        ]);
    }
}
