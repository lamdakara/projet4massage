<?php

namespace App\Controller;

use App\Entity\Order;
use App\Service\MailjetService;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class OrderCancelController extends AbstractController
{
    private $entityManager;

    // Récupération de la BDD
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/commande/erreur/{stripeSessionId}', name: 'order_cancel'), IsGranted('ROLE_USER')]
    public function index($stripeSessionId, MailjetService $mailjetService) : Response
    {
        //Récupérer les élements de Order
        $order = $this->entityManager->getRepository(Order::class)->findOneByStripeSessionId($stripeSessionId);

        $this->addFlash('danger', 'Votre paiement à été réfusé !');
        
        //Si la commande n'est pas du bon utilisateur retour à la page accueil
        if (!$order || $order->getUser() != $this->getUser()) {
            return $this->redirectToRoute('home');
        }

        // Envoyer un email à notre utilisateur pour lui indiquer l'échec de paiement
        $content = "Bonjour " . $order->getUser()->getFirstname() . "<br/>Votre paiement a échouée.<br><br/>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Aperiam expedita fugiat ipsa magnam mollitia optio voluptas! Alias, aliquid dicta ducimus exercitationem facilis, incidunt magni, minus natus nihil odio quos sunt?";
        $mailjetService->sendEmail($order->getUser()->getEmail(), $order->getUser()->getFirstname(), 'Votre paiement sur Lili Giroud a échouée.', $content);

        return $this->render('order_cancel/index.html.twig', [
            'order' => $order
        ]);
    }

}
