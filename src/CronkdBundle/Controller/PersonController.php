<?php
namespace CronkdBundle\Controller;

use CronkdBundle\Entity\Person\Person;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/person")
 */
class PersonController extends CronkdController
{
    /**
     * @Route("", name="person_index")
     * @Template("CronkdBundle:Person:index.html.twig")
     */
    public function indexAction() {
        $kingdom = $this->extractKingdomFromCurrentUser();
        if (empty($kingdom)) {
            throw $this->createAccessDeniedException('You must be logged in to a kingdom to access persons!');
        }

        return [
            'persons' => $kingdom->getPersons()
        ];
    }

    /**
     * @Route("/create", name="person_create")
     * @Template("CronkdBundle:Person:create.html.twig")
     */
    public function createAction(Request $request) {
        $kingdom = $this->extractKingdomFromCurrentUser();
        if (empty($kingdom)) {
            throw $this->createAccessDeniedException('You must be logged in to a kingdom to create a person!');
        }

        $em = $this->getDoctrine()->getManager();

        $person = new Person();
        $person->setKingdom($kingdom);

        $form = $this->createFormBuilder($person)
            ->add('name', TextType::class)
            ->add('attack', IntegerType::class)
            ->add('defense', IntegerType::class)
            ->add('submit', SubmitType::class)
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $person = $form->getData();
            $em->persist($person);
            $em->flush();

            return $this->redirect($this->generateUrl('person_index'));
        }

        return [
            'form' => $form->createView()
        ];
    }
}