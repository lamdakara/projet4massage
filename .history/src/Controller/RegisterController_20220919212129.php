<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegisterType;
use App\Security\LoginFormAuthenticator;
use App\Service\MailjetService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;

class RegisterController extends AbstractController
{
    private $entityManager;

    // Récupération dans la BDD
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/inscription', name: 'register')]
    public function index(Request $request, UserPasswordHasherInterface $passwordHasher, MailjetService $mailjetService, SluggerInterface $slugger, UserAuthenticatorInterface $userAuthenticator, LoginFormAuthenticator $authenticator): Response
    {

        // Sauvegarder les informations dans la BDD
        $notification = null;

        $user = new User();
        $form = $this->createForm(RegisterType::class, $user);

        $form->handleRequest($request);

        //Si envoyé et valide alors 
        if ($form->isSubmitted() && $form->isValid()) {

            $user = $form->getData();

            //Photo
            $imageFile = $form->get('image')->getData();

            // Si un fichier est téléchargé
            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                // On donne un nouveau nom à notre fichier
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();

                // On transfère le fichier dans le dossier uploads dans public 
                try {
                    $imageFile->move(
                        $this->getParameter('image_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    $notification = "L'envoi de votre photo a rencontré un problème";
                }

                // Mise à jour de la propriété 'image' pour stocker le nom de l'image au lieu de son contenu
                $user->setImage($newFilename);
            }

            //Voir si utilisateur n'est pas existant dans la BDD
            $search_email = $this->entityManager->getRepository(User::class)->findOneByEmail($user->getEmail());

            if (!$search_email) {
                // Encodage du mot de passe
                $password = $passwordHasher->hashPassword($user, $user->getPassword());
                $user->setPassword($password);

                //Envoi dans la BDD
                $this->entityManager->persist($user);
                $this->entityManager->flush();

                //Envoi d'un email
                $content = "Bonjour " . $user->getFirstname() . "<br/>Bienvenue sur le site de Lili Giroud, massage madérothérapeutique.<br><br/>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Aperiam expedita fugiat ipsa magnam mollitia optio voluptas! Alias, aliquid dicta ducimus exercitationem facilis, incidunt magni, minus natus nihil odio quos sunt?";
                $mailjetService->sendEmail($user->getEmail(), $user->getFirstname(), 'Bienvenue sur Lili Giroud', $content);

                $this->addFlash('success', 'Votre inscription s\'est correctement déroulée. Vous pouvez dès à présent vous connecter à votre compte');

                // une fois le compte est créer on authentifie automatiquement l'utilisateur
                return $userAuthenticator->authenticateUser(
                    $user,
                    $authenticator,
                    $request
                );

                $notification = "Votre inscription s'est correctement déroulée. Vous pouvez dès à présent vous connecter à votre compte";
            } else {
                $notification = "L'email que vous avez renseignée existe déjà";
                $this->addFlash('danger', "L'email que vous avez renseignée existe déjà");
            }
        }


        // Gérer la vue
        return $this->render('register/index.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
