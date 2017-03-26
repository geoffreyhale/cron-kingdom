<?php
namespace CronkdBundle\Controller\Api;

use CronkdBundle\Entity\NetWorthLog;
use CronkdBundle\Entity\World;
use CronkdBundle\Exceptions\EmptyGraphingDatasetException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/api/netWorth")
 */
class NetWorthController extends Controller
{
    /**
     * @Route("/data", name="networth_data")
     * @ParamConverter()
     * @Method("POST")
     */
    public function dataAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $worldId = $request->get('world');
        $world = $em->getRepository(World::class)->find($worldId);

        $graphingService = $this->get('cronkd.service.graphing');
        try {
            $netWorthData = $graphingService->fetchNetWorthGraphData($world);
        } catch (EmptyGraphingDatasetException $e) {
            return JsonResponse::create(['error' => 'no data']);
        }

        return JsonResponse::create($netWorthData);
    }
}
