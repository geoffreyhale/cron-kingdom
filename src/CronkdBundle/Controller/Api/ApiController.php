<?php
namespace CronkdBundle\Controller\Api;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

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
}