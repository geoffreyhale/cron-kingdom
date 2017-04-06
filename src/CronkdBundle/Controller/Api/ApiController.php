<?php
namespace CronkdBundle\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/api")
 */
abstract class ApiController extends Controller
{
    public function createErrorJsonResponse($error)
    {
        return new JsonResponse([
            'error' => $error,
        ]);
    }

    public function createSerializedJsonResponse($data)
    {
        $serializer = $this->get('jms_serializer');
        $data = $serializer->serialize($data, 'json');

        return Response::create($data, Response::HTTP_OK, [
            'Content-type' => 'application/json'
        ]);
    }
}