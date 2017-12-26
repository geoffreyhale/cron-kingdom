<?php
namespace CronkdBundle\Controller;

use CronkdBundle\Entity\ChatMessage;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/chat")
 */
class ChatController extends CronkdController
{
    /**
     * @Route("", name="chat")
     * @Template
     */
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $kingdom = $this->extractKingdomFromCurrentUser();

        if (empty($kingdom)) {
            throw $this->createAccessDeniedException('You must be logged in to a kingdom to access chat!');
        }

        $message = new ChatMessage();
        $message->setKingdom($kingdom);

        $form = $this->createFormBuilder($message)
            ->add('body', TextType::class)
            ->add('submit', SubmitType::class)
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $message = $form->getData();

            if (!empty($message->getBody())) {
                $em->persist($message);
                $em->flush();
            }

            return $this->redirect($this->generateUrl('chat'));
        }

        $em = $this->getDoctrine()->getManager();
        $messagesByCreatedAt = $em->getRepository(ChatMessage::class)->findBy([], ['createdAt' => 'ASC']);

        if (end($messagesByCreatedAt) instanceof ChatMessage) {
            $kingdom->setLastReadChatMessage(end($messagesByCreatedAt));
            $em->flush();
        }

        return [
            'form' => $form->createView(),
            'messages' => $messagesByCreatedAt
        ];
    }

    /**
     * @Route("/get-unread-message-count", name="get_unread_message_count")
     */
    public function getUnreadMessageCountAction()
    {
        $em = $this->getDoctrine()->getManager();
        $kingdom = $this->extractKingdomFromCurrentUser();

        $unreadMessageCount = $em->getRepository(ChatMessage::class)->getUnreadMessageCount($kingdom);

        return JsonResponse::create((int)$unreadMessageCount);
    }
}
