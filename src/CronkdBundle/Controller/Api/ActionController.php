<?php
namespace CronkdBundle\Controller\Api;

use CronkdBundle\Entity\Kingdom;
use CronkdBundle\Entity\KingdomResource;
use CronkdBundle\Entity\Resource;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/api/action")
 */
class ActionController extends ApiController
{
    /**
     * @Route("/produce", name="action_product")
     * @Method("PUT")
     */
    public function produceAction(Request $request)
    {
        $kingdomId = (int) $request->get('kingdomId');
        $quantity = (int) $request->get('quantity');

        if (empty($kingdomId)) {
            return $this->createErrorJsonResponse('You must pass a parameter `kingdomId` (int)');
        }
        if (empty($quantity) || 0 >= $quantity) {
            return $this->createErrorJsonResponse('You must pass a parameter `quantity` (positive int)');
        }

        $em = $this->getDoctrine()->getManager();
        $kingdom = $em->getRepository(Kingdom::class)->find($kingdomId);
        if (!$kingdom) {
            return $this->createErrorJsonResponse('Invalid Kingdom');
        }

        $materialResource = $em->getRepository(Resource::class)->findOneBy(['name' => 'Material']);
        $civilianResource = $em->getRepository(Resource::class)->findOneBy(['name' => 'Civilian']);
        $availableCivilians = $em->getRepository(KingdomResource::class)->findOneBy([
            'kingdom'  => $kingdom,
            'resource' => $civilianResource,
        ]);

        if (!$availableCivilians || $quantity > $availableCivilians->getQuantity()) {
            return $this->createErrorJsonResponse('Not enough civilians to complete action!');
        }

        $queuePopulator = $this->get('cronkd.queue_populator');
        $civilianQueues = $queuePopulator->build($kingdom, $civilianResource, 8, $quantity);
        $materialQueues = $queuePopulator->build($kingdom, $materialResource, 8, $quantity);

        $availableCivilians->removeQuantity($quantity);
        $em->persist($availableCivilians);
        $em->flush();

        return new JsonResponse([
            'data' => [
                'civilian_queues' => $civilianQueues,
                'material_queues' => $materialQueues,
            ],
        ]);
    }

    /**
     * @Route("/build", name="action_build")
     * @Method("PUT")
     */
    public function buildAction(Request $request)
    {
        $kingdomId = (int) $request->get('kingdomId');
        $quantity = (int) $request->get('quantity');

        if (empty($kingdomId)) {
            return $this->createErrorJsonResponse('You must pass a parameter `kingdomId` (int)');
        }
        if (empty($quantity) || 0 >= $quantity) {
            return $this->createErrorJsonResponse('You must pass a parameter `quantity` (positive int)');
        }

        $em = $this->getDoctrine()->getManager();
        $kingdom = $em->getRepository(Kingdom::class)->find($kingdomId);
        if (!$kingdom) {
            return $this->createErrorJsonResponse('Invalid Kingdom');
        }

        $housingResource = $em->getRepository(Resource::class)->findOneBy(['name' => 'Housing']);
        $materialResource = $em->getRepository(Resource::class)->findOneBy(['name' => 'Material']);
        $civilianResource = $em->getRepository(Resource::class)->findOneBy(['name' => 'Civilian']);
        $availableCivilians = $em->getRepository(KingdomResource::class)->findOneBy([
            'kingdom'  => $kingdom,
            'resource' => $civilianResource,
        ]);
        $availableMaterials = $em->getRepository(KingdomResource::class)->findOneBy([
            'kingdom'  => $kingdom,
            'resource' => $materialResource,
        ]);

        if (!$availableCivilians || $quantity > $availableCivilians->getQuantity()) {
            return $this->createErrorJsonResponse('Not enough civilians to complete action');
        }
        if (!$availableMaterials || $quantity > $availableMaterials->getQuantity()) {
            return $this->createErrorJsonResponse('Note enough materials to complete action');
        }

        $queuePopulator = $this->get('cronkd.queue_populator');
        $civilianQueues = $queuePopulator->build($kingdom, $civilianResource, 16, $quantity);
        $housingQueues = $queuePopulator->build($kingdom, $housingResource, 16, $quantity);

        $availableMaterials->removeQuantity($quantity);
        $em->persist($availableMaterials);
        $availableCivilians->removeQuantity($quantity);
        $em->persist($availableCivilians);
        $em->flush();

        return new JsonResponse([
            'data' => [
                'civilian_queues' => $civilianQueues,
                'housing_queues' => $housingQueues,
            ],
        ]);
    }

    /**
     * @Route("/train", name="action_train")
     * @Method("PUT")
     */
    public function trainAction(Request $request)
    {
        $kingdomId = (int) $request->get('kingdomId');
        $quantity = (int) $request->get('quantity');

        if (empty($kingdomId)) {
            return $this->createErrorJsonResponse('You must pass a parameter `kingdomId` (int)');
        }
        if (empty($quantity) || 0 >= $quantity) {
            return $this->createErrorJsonResponse('You must pass a parameter `quantity` (positive int)');
        }

        $em = $this->getDoctrine()->getManager();
        $kingdom = $em->getRepository(Kingdom::class)->find($kingdomId);
        if (!$kingdom) {
            return $this->createErrorJsonResponse('Invalid Kingdom');
        }

        $militaryResource = $em->getRepository(Resource::class)->findOneBy(['name' => 'Military']);
        $civilianResource = $em->getRepository(Resource::class)->findOneBy(['name' => 'Civilian']);
        $availableCivilians = $em->getRepository(KingdomResource::class)->findOneBy([
            'kingdom'  => $kingdom,
            'resource' => $civilianResource,
        ]);

        if (!$availableCivilians || $quantity > $availableCivilians->getQuantity()) {
            return $this->createErrorJsonResponse('Not enough civilians to complete action!');
        }

        $queuePopulator = $this->get('cronkd.queue_populator');
        $militaryQueues = $queuePopulator->build($kingdom, $militaryResource, 24, $quantity);

        $availableCivilians->removeQuantity($quantity);
        $em->persist($availableCivilians);
        $em->flush();

        return new JsonResponse([
            'data' => [
                'military_queues' => $militaryQueues,
            ],
        ]);
    }
}
