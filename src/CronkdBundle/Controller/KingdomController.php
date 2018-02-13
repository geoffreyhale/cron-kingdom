<?php
namespace CronkdBundle\Controller;

use CronkdBundle\Entity\Kingdom;
use CronkdBundle\Entity\World;
use CronkdBundle\Form\KingdomType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/kingdom")
 */
class KingdomController extends CronkdController
{
    /**
     * @Route("", name="kingdom_show")
     * @Template("CronkdBundle:Kingdom:show.html.twig")
     */
    public function indexAction()
    {
        return [];
    }

    /**
     * @Route("/create/{id}", name="kingdom_create")
     * @Method({"GET", "POST"})
     * @Template("CronkdBundle:Kingdom:create.html.twig")
     */
    public function createAction(Request $request, $id = null)
    {
        $em = $this->getDoctrine()->getManager();
        if (null !== $id) {
            $world = $em->getRepository(World::class)->find($id);
        } else {
            $world = $em->getRepository(World::class)->findActiveWorld();
        }
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

            if ($world->isActive()) {
                return $this->redirectToRoute('home');
            }

            return $this->redirectToRoute('world_show', ['id' => $world->getId()]);
        }

        return [
            'form' => $form->createView(),
        ];
    }

    /**
     * @Route("/reset", name="kingdom_reset")
     */
    public function resetAction()
    {
        $kingdom = $this->extractKingdomFromCurrentUser();
        $this->validateUserOwnsKingdom($kingdom);

        $kingdomManager = $this->get('cronkd.manager.kingdom');
        $kingdomManager->resetKingdom($kingdom);

        $this->get('session')->getFlashBag()->add('success', 'Your kingdom has been successfully reset.');

        return $this->redirectToRoute('world_show', ['id' => $kingdom->getWorld()->getId()]);
    }

    /**
     * @Route("/get-kingdom-panel-component", name="get_kingdom_panel_component")
     */
    public function getKingdomPanelComponentAction()
    {
        return $this->render('CronkdBundle:Components:fullKingdomStatsPanel.html.twig', [
            'kingdom' => $this->extractKingdomFromCurrentUser()
        ]);
    }
}
