<?php
namespace CronkdBundle\Form\Policy;

use CronkdBundle\Entity\Policy\Policy;
use CronkdBundle\Entity\Policy\PolicyResource;
use CronkdBundle\Entity\Resource\Resource;
use CronkdBundle\Entity\World;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PolicyResourceType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('resource', EntityType::class, [
                'required'      => true,
                'class'         => Resource::class,
                'placeholder'   => '--- Select Resource ---',
                'query_builder' => function(EntityRepository $er) use ($options) {
                    $qb = $er->createQueryBuilder('r');
                    $qb->orderBy('r.name', 'ASC');
                    if ($options['world'] instanceof World) {
                        $qb->where('r.world = :world');
                        $qb->setParameters([
                            'world' => $options['world'],
                        ]);
                    }

                    return $qb;
                },
            ])
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
            'data_class' => PolicyResource::class,
            'world'      => null,
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
