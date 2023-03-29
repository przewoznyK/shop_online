<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\Product;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;

class AddProductFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class)
            ->add('description', TextType::class)
            ->add('price', NumberType::class, [
                'scale' => 2,
                'attr' => [
                    'step' => '0.01',
                    'min' => '0'
                ]
            ])
            //->add('category_id')
            //->add('created_at')
            //->add('updated_at')
            ->add('category', EntityType::class, [
                'class' => Category::class
            ])
            ->add('my_files', FileType::class, [
                'mapped' => false,
                'label' => 'Upload Images',
                'multiple' => true,
                'required' => $options['image_required']
            ])
            ->add('is_public', CheckboxType::class,[
                'attr' => ['class' => 'form-check-input'],
                'required' => false
            ])
            ->add('quantity', IntegerType::class,[
                'attr' => [
                      'min' => '1'
                ]
              
            ])
            ->add('location', TextType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
            'image_required' => true,
            'validation_groups' => ['Default'],
        ]);

        $resolver->setAllowedTypes('validation_groups', ['string', 'array']);
    }
}
