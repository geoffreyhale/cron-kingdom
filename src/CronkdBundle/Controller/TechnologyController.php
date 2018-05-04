<?php
namespace CronkdBundle\Controller;

use CronkdBundle\Entity\Technology\Technology;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * @Route("/tech")
 */
class TechnologyController extends CronkdController
{
    /**
     * @Route("/", name="technology_index")
     * @Method({"GET", "POST"})
     * @Template("CronkdBundle:Technology:index.html.twig")
     */
    public function indexAction()
    {
        $kingdom = $this->extractKingdomFromCurrentUser();
        $this->validateWorldIsActive($kingdom);
        $this->validateUserOwnsKingdom($kingdom);

        $em = $this->getDoctrine()->getManager();
        $technologies = $em->getRepository(Technology::class)
            ->findBy(['world' => $kingdom->getWorld()]);

        return [
            'kingdom'      => $kingdom,
            'types'        => Technology::TYPES,
            'technologies' => $technologies,
        ];
    }
}
