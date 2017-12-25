<?php
namespace CronkdBundle\Controller;

use CronkdBundle\Entity\MapTile;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/map")
 */
class MapController extends CronkdController
{
    /**
     * @Route("", name="map")
     * @Template("CronkdBundle:Map:index.html.twig")
     */
    public function indexAction() {
        $em = $this->getDoctrine()->getManager();

        $kingdom = $this->extractKingdomFromCurrentUser();

        $mapTiles = $em->getRepository(MapTile::class)->findAll();

        return [
            'kingdom' => $kingdom,
            'mapTiles' => $mapTiles,
        ];
    }

    /**
     * @Route("/kingdom-move", name="kingdom_move")
     * @Method({"GET", "POST"})
     */
    public function kingdomMove(Request $request)
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

        return $this->render('CronkdBundle:Map:mapTileForm.html.twig', [
            'kingdom' => $kingdom,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("", name="renderMap")
     * @Template("CronkdBundle:Components:map.html.twig")
     */
    public function renderMapAction() {
        $em = $this->getDoctrine()->getManager();

        $kingdom = $this->extractKingdomFromCurrentUser();

        $mapTiles = $em->getRepository(MapTile::class)->findAll();

        $xMin = min(array_map(function($mp) {
            return $mp->getX();
        }, $mapTiles));
        $xMax = max(array_map(function($mp) {
            return $mp->getX();
        }, $mapTiles));

        $yMin = min(array_map(function($mp) {
            return $mp->getY();
        }, $mapTiles));
        $yMax = max(array_map(function($mp) {
            return $mp->getY();
        }, $mapTiles));

        return [
            'kingdom' => $kingdom,
            'mapTiles' => $mapTiles,
            'xMin' => $xMin,
            'xMax' => $xMax,
            'yMin' => $yMin,
            'yMax' => $yMax,
        ];
    }
}