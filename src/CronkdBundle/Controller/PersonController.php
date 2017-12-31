<?php
namespace CronkdBundle\Controller;

use CronkdBundle\Entity\Person\Person;
use CronkdBundle\Manager\KingdomManager;
use CronkdBundle\Manager\ResourceManager;
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
            /** @var Person $person */
            $person = $form->getData();

            /** @var KingdomManager $kingdomManager */
            $kingdomManager = $this->get('cronkd.manager.kingdom');
            /** @var ResourceManager $resourceManager */
            $resourceManager = $this->get('cronkd.manager.resource');

            $attackResourceName = 'Attacker';
            $defenseResourceName = 'Defender';

            $kingdomAttackerCount = $kingdomManager->lookupResource($kingdom, $attackResourceName)->getQuantity();
            $kingdomDefenderCount = $kingdomManager->lookupResource($kingdom, $defenseResourceName)->getQuantity();

            $errors = [];
            if ($person->getAttack() > $kingdomAttackerCount) {
                $errors[] = 'You do not have enough Attackers ('
                    . $kingdomAttackerCount
                    . ') to create a Person with '
                    . $person->getAttack()
                    . ' Attack.'
                ;
            }
            if ($person->getDefense() > $kingdomDefenderCount) {
                $errors[] = 'You do not have enough Defenders ('
                    . $kingdomDefenderCount
                    . ') to create a Person with '
                    . $person->getDefense()
                    . ' Defense.'
                ;
            }
            if ($errors) {
                throw new \Exception(implode(' ', $errors));
            }

            $kingdomManager->modifyResources($kingdom, $resourceManager->get($attackResourceName), -1 * $person->getAttack(), false);
            $kingdomManager->modifyResources($kingdom, $resourceManager->get($defenseResourceName), -1 * $person->getDefense(), false);

            $kingdomManager->calculateNetWorth($kingdom);
            //@todo we should update kd attack and defense values too, not just net worth; but should refactor TickService->attemptTick to not do any premature flushing, then break out a kingdom-specific function that can be run here (or anywhere we want to update a KD's NW and other attributes after some event/action)

            $em->persist($person);
            $em->flush();

            return $this->redirect($this->generateUrl('person_index'));
        }

        return [
            'form' => $form->createView()
        ];
    }
}