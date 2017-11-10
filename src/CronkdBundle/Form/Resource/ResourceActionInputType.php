<?php
namespace CronkdBundle\Form\Resource;

use CronkdBundle\Entity\Resource\Resource;
use CronkdBundle\Entity\Resource\ResourceActionInput;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ResourceActionInputType extends AbstractType
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
                    if (count($options['unavailable_resources'])) {
                        $qb->andWhere($qb->expr()->notIn('r.id', $options['unavailable_resources']));
                    }
                    if ($options['world'] instanceof World) {
                        $qb->join('r.world', 'w');
                        $qb->andWhere('w.id = :world');
                        $qb->setParameter('world', $options['resource']->getWorld()->getId());
                    }
                    if ($options['resource'] instanceof Resource) {
                        $qb->andWhere('r.id != :resource');
                        $qb->setParameter('resource', $options['resource']);
                    }

                    return $qb;
                },
            ])
            ->add('inputQuantity', IntegerType::class, [
                'required' => true,
                'label'    => 'Quantity',
            ])
            ->add('queueSize', IntegerType::class, [
                'required' => false,
                'label'    => 'Queue Size',
            ])
            ->add('requeue', CheckboxType::class, [
                'label'    => 'Send back to Queue? (Will be removed otherwise!)',
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
            'data_class'            => ResourceActionInput::class,
            'unavailable_resources' => [],
            'resource'              => [],
            'world'                 => null,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'cronkdbundle_resource_action_input';
    }
}
