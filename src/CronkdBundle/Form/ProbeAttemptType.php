<?php
namespace CronkdBundle\Form;

use CronkdBundle\Entity\Kingdom;
use CronkdBundle\Exceptions\InvalidKingdomStateException;
use CronkdBundle\Exceptions\InvalidSettingsToParseException;
use CronkdBundle\Model\KingdomState;
use CronkdBundle\Model\ProbeAttempt;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProbeAttemptType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if (empty($options['settings'])) {
            throw new InvalidSettingsToParseException();
        }
        if (!$options['kingdomState'] instanceof KingdomState) {
            throw new InvalidKingdomStateException();
        }

        foreach ($options['settings']['resources'] as $resourceName => $resourceSetting) {
            if ($resourceSetting['probe_power'] > 0 && $options['kingdomState']->hasAvailableResource($resourceName)) {
                $builder->add($resourceName, TextType::class, [
                    'required' => true,
                ]);
            }
        }

        $builder
            ->add('target', EntityType::class, [
                'required' => true,
                'class' => Kingdom::class,
                'placeholder' => '--- Select Kingdom ---',
                'query_builder' => function(EntityRepository $er) use ($options) {
                    $qb = $er->createQueryBuilder('k');
                    $qb->orderBy('k.name', 'ASC');
                    if ($options['kingdomState']->getKingdom() instanceof Kingdom) {
                        $qb->where('k.world = :world');
                        $qb->andWhere('k.id != :kingdom');
                        $qb->setParameters([
                            'world' => $options['kingdomState']->getKingdom()->getWorld(),
                            'kingdom' => $options['kingdomState']->getKingdom(),
                        ]);
                    }

                    return $qb;
                },
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Hack',
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class'    => ProbeAttempt::class,
            'kingdomState'  => null,
            'settings'      => [],
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
