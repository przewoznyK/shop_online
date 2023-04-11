<?php

namespace App\Form;

use App\Entity\Delivery;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DeliveryFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('type', ChoiceType::class,[
                'choices' => [
                    'personal pickup' => 'personal_pickup',
                    'parcel locker' => 'parcel_locker',
                    'courier' => 'courier',
                ]
            ])
            ->add('personal_pickup')
            ->add('delivery_time')
            ->add('price', IntegerType::class)
            ->add('submit', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Delivery::class,
        ]);
    }
}
