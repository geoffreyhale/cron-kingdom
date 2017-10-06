<?php
namespace CronkdBundle\Form\Resource;

use CronkdBundle\Entity\Resource\ResourceAction;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ResourceActionType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('verb', TextType::class, [
                'required' => true,
                'label'    => 'Action Name',
            ])
            ->add('outputQuantity', IntegerType::class, [
                'required' => true,
                'label'    => 'Quantity',
            ])
            ->add('queueSize', IntegerType::class, [
                'required' => true,
                'label'    => 'Queue Size (number of Ticks to fully pay out)',
            ])
            ->add('submit', SubmitType::class)
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ResourceAction::class,
            'resource'   => null,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'cronkdbundle_resource_action';
    }
}
