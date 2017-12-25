<?php
namespace CronkdBundle\Form\Resource;

use CronkdBundle\Entity\Resource\Resource;
use CronkdBundle\Entity\Resource\ResourceHousing;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ResourceHousingType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('referencedResource', EntityType::class, [
                'required'      => true,
                'class'         => Resource::class,
                'label'         => 'Resource',
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
            ->add('submit', SubmitType::class)
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class'            => ResourceHousing::class,
            'unavailable_resources' => [],
            'resource'              => null,
            'world'                 => null,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'cronkdbundle_resource_housing';
    }
}
