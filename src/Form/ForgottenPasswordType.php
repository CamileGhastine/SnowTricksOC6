<?php

namespace App\Form;

use App\Entity\UserProto;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ForgottenPasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', EmailType::class, [ 'label' => 'Saisir votre adresse courriel :' ])
            ->add('password', HiddenType::class, [ 'required' => false, 'empty_data' => 'Password123456789'])
            ->add('confirm_password', HiddenType::class, [ 'required' => false, 'empty_data' => 'Password123456789'])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => UserProto::class,
            'translation_domain' => 'forms',
        ]);
    }
}
