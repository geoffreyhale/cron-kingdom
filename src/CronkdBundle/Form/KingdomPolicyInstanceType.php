<?php
namespace CronkdBundle\Form;

use CronkdBundle\Entity\Policy\KingdomPolicyInstance;
use CronkdBundle\Form\DataTransformer\PolicyToIdTransformer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class KingdomPolicyInstanceType extends AbstractType
{
    /** @var EntityManagerInterface  */
    private $manager;

    public function __construct(EntityManagerInterface $manager)
    {
        $this->manager = $manager;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('policy', HiddenType::class)
        ;

        $builder->get('policy')
            ->addModelTransformer(new PolicyToIdTransformer($this->manager));
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => KingdomPolicyInstance::class,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'cronkdbundle_policy_instance';
    }
}
