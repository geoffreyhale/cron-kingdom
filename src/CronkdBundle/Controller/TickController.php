<?php
namespace CronkdBundle\Controller;

use CronkdBundle\Entity\Resource\Resource;
use CronkdBundle\Entity\World;
use CronkdBundle\Form\Resource\ResourceType;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * @Route("/tick")
 */
class TickController extends Controller
{
    /**
     * @Route("/perform", name="tick_perform")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function performAction()
    {
        $application = new Application($this->get('kernel'));
        $application->setAutoExit(false);

        $input = new ArrayInput(['command' => 'cronkd:tick',]);
        $output = new NullOutput();
        $application->run($input, $output);

        return $this->redirectToRoute('home');
    }
}
