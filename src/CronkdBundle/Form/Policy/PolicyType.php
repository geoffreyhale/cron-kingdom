<?php
namespace CronkdBundle\Form\Policy;

use CronkdBundle\Entity\Policy\Policy;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PolicyType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'required' => true,
            ])
            ->add('description', TextareaType::class)
            ->add('outputMultiplier', IntegerType::class)
            ->add('attackMultiplier', IntegerType::class)
            ->add('defenseMultiplier', IntegerType::class)
            ->add('probePowerMultiplier', IntegerType::class)
            ->add('capacityMultiplier', IntegerType::class)
            ->add('queueSizeModifier', IntegerType::class)
            ->add('spoilOfWarAttackCaptureMultiplier', IntegerType::class)
            ->add('spoilOfWarDefenseCaptureMultiplier', IntegerType::class)
            ->add('submit', SubmitType::class, [
                'label' => 'Save',
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Policy::class,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'cronkdbundle_policy';
    }
}
