<?php

namespace App\Form;

use App\Entity\OrderProduct;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OrderProductFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            //->add('product')
            //->add('price')
            //->add('ispaid')
            
            //->add('delivery_type')
            
            ->add('name')
            ->add('lastName')
            ->add('email')
            ->add('phoneNumber')
            ->add('address')
            ->add('comment')
            ->add('final_location')
            //->add('paymentMethod')
            //->add('owner')
            //->add('buyer')
            ->add('submit', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => OrderProduct::class,
        ]);
    }
}
