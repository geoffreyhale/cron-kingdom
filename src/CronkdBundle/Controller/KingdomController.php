<?php
namespace CronkdBundle\Controller;

use CronkdBundle\Entity\Kingdom;
use CronkdBundle\Entity\World;
use CronkdBundle\Form\KingdomType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/kingdom")
 */
class KingdomController extends Controller
{
    /**
     * @Route("/create", name="kingdom_create")
     * @Method({"GET", "POST"})
     * @Template()
     */
    public function createAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $world = $em->getRepository(World::class)->findOneBy(['active' => true]);
        if (!$world) {
            throw $this->createNotFoundException('No active world found!');
        }

        $currentUser = $this->getUser();
        $userHasKingdom = $em->getRepository(Kingdom::class)->userHasKingdom($currentUser, $world);
        if ($userHasKingdom) {
            throw $this->createAccessDeniedException('Cannot have more than one kingdom!');
        }

        $kingdom = new Kingdom();
        $form = $this->createForm(KingdomType::class, $kingdom);
        $form->handleRequest($request);
        if ($form->isValid()) {
            $kingdomManager = $this->get('cronkd.manager.kingdom');
            $kingdomManager->createKingdom($kingdom, $world, $currentUser);

            return $this->redirectToRoute('homepage');
        }

        return [
            'form' => $form->createView(),
        ];
    }
}
