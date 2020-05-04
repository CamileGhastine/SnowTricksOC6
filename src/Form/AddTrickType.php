<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\Trick;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AddTrickType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title')
            ->add('description')
            ->add('categories',
                EntityType::class,
                ['class' => Category::class,
                    'choice_label' => 'title',
                    'multiple' => true,
                    'expanded' => true,
                    'by_reference' => false,
            ])
            ->add('images', CollectionType::class, [
                'entry_type' => ImageType::class,
//                'entry_options' => [
//                    'error_bubbling' => false,
//                ],
                'allow_add' => true,
                'by_reference' => false,
//                'error_bubbling' =>true
            ])
            ->add('videos', CollectionType::class, [
                'entry_type' => VideoType::class,
//                'entry_options' => [
//                    'error_bubbling' => false,
//                ],
                'allow_add' => true,
                'by_reference' => false,
//                'error_bubbling' => true
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Trick::class,
            'translation_domain' => 'forms',
        ]);
    }
}
