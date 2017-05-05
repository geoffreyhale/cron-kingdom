<?php
namespace CronkdBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class ChatController extends Controller
{
    /**
     * @Route("/chat", name="chat")
     * @Template
     */
    public function indexAction()
    {
        return [];
    }
}
