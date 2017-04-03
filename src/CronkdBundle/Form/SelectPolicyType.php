<?php
namespace CronkdBundle\Form;

use CronkdBundle\Entity\KingdomPolicy;
use CronkdBundle\Entity\Policy;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SelectPolicyType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('policy', EntityType::class, [
                'required' => true,
                'class' => Policy::class,
                'placeholder' => '--- Select Policy ---',
                'query_builder' => function(EntityRepository $er) use ($options) {
                    return $er->createQueryBuilder('kp')
                        ->orderBy('kp.name', 'ASC')
                    ;
                },
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Select',
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => KingdomPolicy::class
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'cronkdbundle_select_policy';
    }
}
