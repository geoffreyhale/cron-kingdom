<?php
namespace CronkdBundle\Form;

use CronkdBundle\Entity\Resource\Resource;
use CronkdBundle\Entity\Resource\ResourceType as ResourceTypeEntity;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ResourceType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('type', EntityType::class, [
                'required'    => true,
                'placeholder' => 'Please select a type...',
                'class'       => ResourceTypeEntity::class,
            ])
            ->add('name', TextType::class, [
                'required' => true,
            ])
            ->add('value', IntegerType::class, [
                'required' => true,
                'label'    => 'Value (for liquidity and net worth)',
            ])
            ->add('startingAmount', IntegerType::class, [
                'required' => true,
                'label'    => 'Starting Amount',
            ])
            ->add('canBeProbed', CheckboxType::class, [
                'label'    => 'Can Be Probed?',
                'required' => false,
            ])
            ->add('canBeProduced', CheckboxType::class, [
                'label'    => 'Can Be Produced?',
                'required' => false,
            ])
            ->add('attack', IntegerType::class, [
                'required' => true,
                'label'    => 'Attack Power',
            ])
            ->add('defense', IntegerType::class, [
                'required' => true,
                'label'    => 'Defense Power',
            ])
            ->add('probePower', IntegerType::class, [
                'required' => true,
                'label'    => 'Probe Power',
            ])
            ->add('capacity', IntegerType::class, [
                'required' => true,
            ])
            ->add('spoilOfWar', CheckboxType::class, [
                'label'    => 'Is Spoil of War?',
                'required' => false,
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
            'data_class' => Resource::class,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'cronkdbundle_resource';
    }
}
