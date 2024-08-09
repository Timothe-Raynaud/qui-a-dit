<?php

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\UX\Dropzone\Form\DropzoneType;
use Symfony\Component\Form\FormBuilderInterface;

class ImportType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('password', PasswordType::class, [
                'label' => false,
                'attr' => [
                    'placeholder' => 'Mot de passe'
                ]
            ])
            ->add('file', DropzoneType::class, [
                'label' => false,
            ])
        ;
    }

}