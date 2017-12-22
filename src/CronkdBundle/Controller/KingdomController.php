<?php
namespace CronkdBundle\Controller;

use CronkdBundle\Entity\Kingdom;
use CronkdBundle\Entity\MapTile;
use CronkdBundle\Entity\World;
use CronkdBundle\Form\KingdomType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/kingdom")
 */
class KingdomController extends CronkdController
{
    /**
     * @Route("/create/{id}", name="kingdom_create")
     * @Method({"GET", "POST"})
     * @Template()
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
                return $this->redirectToRoute('homepage');
            }

            return $this->redirectToRoute('world_show', ['id' => $world->getId()]);
        }

        return [
            'form' => $form->createView(),
        ];
    }

    /**
     * @Route("/map-tile", name="kingdom_map_tile")
     * @Method({"GET", "POST"})
     */
    public function mapTile(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        /**
         * If origin map tile (0,0) does not exist, create it
         */
        $originMapTile = $em->getRepository(MapTile::class)->findOneBy(['x' => 0, 'y' => 0]);
        if (!$originMapTile) {
            $originMapTile = new MapTile();
            $originMapTile->setX(0);
            $originMapTile->setY(0);
            $em->persist($originMapTile);
            $em->flush();
        }

        /**
         * Kingdom does not have a map tile, set them to origin
         */
        /** @var Kingdom $kingdom */
        $kingdom = $this->extractKingdomFromCurrentUser();
        if (!$kingdom->getMapTile()) {
            $kingdom->setMapTile($originMapTile);
            $em->persist($kingdom);
            $em->flush();
        }

        $mapTile = new MapTile();

        $form = $this->createFormBuilder($mapTile)
            ->add('x', IntegerType::class)
            ->add('y', IntegerType::class)
            ->add('submit', SubmitType::class, [
                'label' => 'Move',
            ])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();

            /**
             * If new tile is not accessible, throw Exception
             * For now, must be 1 step N, S, E, or W
             */
            $targetTileStepsFromCurrent = 0;
            $targetTileStepsFromCurrent += abs($kingdom->getMapTile()->getX() - $formData->getX());
            $targetTileStepsFromCurrent += abs($kingdom->getMapTile()->getY() - $formData->getY());
            if ($targetTileStepsFromCurrent > 1) {
                throw new \Exception("Cannot move more than 1 step North, South, East, or West.");
            }

            $mapTile = $em->getRepository(MapTile::class)->findOneBy([
                'x' => $formData->getX(),
                'y' => $formData->getY(),
            ]);
            if (!$mapTile) {
                $mapTile = new MapTile();
                $mapTile->setX($formData->getX());
                $mapTile->setY($formData->getY());
                $em->persist($mapTile);
            }

            $kingdom->setMapTile($mapTile);
            $em->flush();

            return $this->redirectToRoute('map');
        }

        return $this->render('CronkdBundle:Kingdom:mapTileForm.html.twig', [
            'kingdom' => $kingdom,
            'form' => $form->createView(),
        ]);
    }
}
