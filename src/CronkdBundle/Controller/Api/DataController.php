<?php
namespace CronkdBundle\Controller\Api;

use CronkdBundle\Entity\Kingdom;
use CronkdBundle\Entity\World;
use CronkdBundle\Exceptions\EmptyGraphingDatasetException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/api/data")
 */
class DataController extends Controller
{
    /**
     * @Route("/world_networth", name="data_world_net_worth")
     * @Method("POST")
     */
    public function worldNetWorthAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $worldId = $request->get('world');
        $world = $em->getRepository(World::class)->find($worldId);

        $minTick   = 0;
        $maxTick   = $world->getTick();
        $pastTicks = empty($request->get('ticks')) ? 25 : $request->get('ticks');
        if ('all' != $pastTicks) {
            $minTick = $maxTick - $pastTicks;
        }

        $graphingService = $this->get('cronkd.service.graphing');
        try {
            $netWorthData = $graphingService->fetchNetWorthGraphData($world, $minTick, $maxTick);
        } catch (EmptyGraphingDatasetException $e) {
            return JsonResponse::create(['error' => 'no data']);
        }

        return JsonResponse::create($netWorthData);
    }

    /**
     * @Route("/kingdom_composition", name="data_kingdom_composition")
     * @Method("POST")
     */
    public function kingdomCompositionAction(Request $request)
    {
        $user = $this->getUser();
        $em = $this->getDoctrine()->getManager();

        $kingdomId = $request->get('kingdom');
        $kingdom = $em->getRepository(Kingdom::class)->find($kingdomId);
        if ($user != $kingdom->getUser()) {
            return JsonResponse::create(['error' => 'invalid user']);
        }

        $graphingService = $this->get('cronkd.service.graphing');
        try {
            $kingdomCompositionData = $graphingService->fetchKingdomCompositionData($kingdom);
        } catch (EmptyGraphingDatasetException $e) {
            return JsonResponse::create(['error' => 'no data']);
        }

        return JsonResponse::create($kingdomCompositionData);
    }

    /**
     * @Route("/population_capacity", name="data_population_capacity")
     * @Method("POST")
     */
    public function populationCapacityAction(Request $request)
    {
        $user = $this->getUser();
        $em = $this->getDoctrine()->getManager();

        $kingdomId = $request->get('kingdom');
        $kingdom = $em->getRepository(Kingdom::class)->find($kingdomId);
        if ($user != $kingdom->getUser()) {
            return JsonResponse::create(['error' => 'invalid user']);
        }

        $graphingService = $this->get('cronkd.service.graphing');
        try {
            $populationCapacityData = $graphingService->fetchPopulationCapacityGraphData($kingdom);
        } catch (EmptyGraphingDatasetException $e) {
            return JsonResponse::create(['error' => 'no data']);
        }

        return JsonResponse::create($populationCapacityData);
    }
}
