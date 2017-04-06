<?php
namespace CronkdBundle\Controller;

use CronkdBundle\Form\UserType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/user")
 */
class UserController extends Controller
{
    /**
     * @Route("/", name="user_index")
     * @Method({"GET", "POST"})
     * @Template()
     */
    public function indexAction(Request $request)
    {
        $user = $this->getUser();

        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();

                if ($user->getVacation()) {
                    $em->persist($user);
                }

                if (!$user->getVacation()) {
                    //verify that 24 hours has passed
                }

                $em->flush();
            }
        }

        return [
            'form' => $form->createView()
        ];
    }
}
