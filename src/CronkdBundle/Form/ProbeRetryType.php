<?php
namespace CronkdBundle\Form;

use CronkdBundle\Entity\Kingdom;
use CronkdBundle\Model\ProbeAttempt;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProbeRetryType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        foreach ($options['quantities'] as $resourceName => $quantity) {
            $builder->add($resourceName, HiddenType::class, [
                'required' => true,
                'data'     => $quantity,
            ]);
        }

        $builder
            ->add('target', HiddenType::class, [
                'required' => true,
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Re-Attempt Hack',
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'sourceKingdom' => null,
            'quantities'    => [],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'cronkdbundle_probe_attempt';
    }
}
