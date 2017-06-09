<?php
namespace CronkdBundle\Form;

use CronkdBundle\Entity\Kingdom;
use CronkdBundle\Entity\World;
use CronkdBundle\Exceptions\InvalidKingdomStateException;
use CronkdBundle\Exceptions\InvalidSettingsToParseException;
use CronkdBundle\Model\KingdomState;
use CronkdBundle\Model\ProbeAttempt;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class WorldType extends AbstractType
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
            ->add('startTime', TextType::class, [
                'required' => true,
            ])
            ->add('endTime', TextType::class, [
                'required' => true,
            ])
            ->add('tickInterval', IntegerType::class, [
                'required' => true,
                'label' => 'Tick Interval (in minutes)',
            ])
            ->add('submit', SubmitType::class)
        ;

        $builder->get('startTime')
            ->addModelTransformer(new CallbackTransformer(
                function ($datetime) {
                    if (null === $datetime || !$datetime instanceof \DateTime) {
                        return '';
                    }

                    return $datetime->format('m/d/Y h:i A');
                },
                function ($string) {
                    return new \DateTime($string);
                }
            ))
        ;

        $builder->get('endTime')
            ->addModelTransformer(new CallbackTransformer(
                function ($datetime) {
                    if (null === $datetime || !$datetime instanceof \DateTime) {
                        return '';
                    }

                    return $datetime->format('m/d/Y h:i A');
                },
                function ($string) {
                    return new \DateTime($string);
                }
            ))
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class'    => World::class,
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
