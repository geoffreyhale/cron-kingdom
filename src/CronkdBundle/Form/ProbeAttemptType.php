<?php
namespace CronkdBundle\Form;

use CronkdBundle\Entity\Kingdom;
use CronkdBundle\Model\ProbeAttempt;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProbeAttemptType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('target', EntityType::class, [
                'required' => true,
                'class' => Kingdom::class,
                'placeholder' => '--- Select Kingdom ---',
                'query_builder' => function(EntityRepository $er) use ($options) {
                    $qb = $er->createQueryBuilder('k');
                    $qb->orderBy('k.name', 'ASC');
                    if ($options['sourceKingdom'] instanceof Kingdom) {
                        $qb->where('k.world = :world');
                        $qb->andWhere('k.id != :kingdom');
                        $qb->setParameters([
                            'world' => $options['sourceKingdom']->getWorld(),
                            'kingdom' => $options['sourceKingdom'],
                        ]);
                    }

                    return $qb;
                },
            ])
            ->add('quantity', NumberType::class, [
                'required' => true,
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Send',
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ProbeAttempt::class,
            'sourceKingdom' => null,
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
