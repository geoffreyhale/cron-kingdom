<?php
namespace CronkdBundle\Controller;

use CronkdBundle\Entity\Kingdom;
use CronkdBundle\Entity\KingdomResource;
use CronkdBundle\Entity\Resource;
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
            $civilianResource = $em->getRepository(Resource::class)->findOneBy(['name' => Resource::CIVILIAN]);
            if (!$civilianResource) {
                $this->createNotFoundException('Civilian resource does not exist!');
            }

            $kingdomResource = new KingdomResource();
            $kingdomResource->setKingdom($kingdom);
            $kingdomResource->setResource($civilianResource);
            $kingdomResource->setQuantity(10);

            $kingdom->setWorld($world);
            $kingdom->setUser($this->getUser());
            $kingdom->addResource($kingdomResource);
            $kingdom->setNetWorth(0);

            $em->persist($kingdom);
            $em->flush();

            return $this->redirectToRoute('kingdom_show', ['id' => $kingdom->getId()]);
        }

        return [
            'form' => $form->createView(),
        ];
    }

    /**
     * @Route("/{id}/show", name="kingdom_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction(Kingdom $kingdom)
    {
        $currentUser = $this->getUser();
        if ($currentUser != $kingdom->getUser()) {
            throw $this->createAccessDeniedException('This is not your kingdom!');
        }

        $kingdomManager = $this->get('cronkd.manager.kingdom');
        $queues = $kingdomManager->getResourceQueues($kingdom);

        return [
            'kingdom'          => $kingdom,
            'queues'           => $queues,
            'kingdomResources' => $kingdom->getResources(),
        ];
    }
}
