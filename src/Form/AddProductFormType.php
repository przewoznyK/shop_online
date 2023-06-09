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
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;

class AddProductFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class)
            ->add('description', TextareaType::class)
            ->add('price', NumberType::class, [
                'label' => 'Price $',
                'scale' => 2,
                'attr' => [
                    'step' => '0.01',
                    'min' => '0',
                    
                ]
            ])
            //->add('category_id')
            //->add('created_at')
            //->add('updated_at')
            ->add('category', EntityType::class, [
                'class' => Category::class
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
            ->add('submit', SubmitType::class,[
                'label' => 'Save and add delivery'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
        ]);
    }
}
