<?php

namespace App\Controller;

use App\Entity\Care;
use App\Form\CareType;
use App\Repository\CareRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/massage')]
class MassageController extends AbstractController
{
    #[Route('/', name: 'app_massage_index', methods: ['GET'])]
    public function index(CareRepository $massageRepository): Response
    {
        return $this->render('massage/index.html.twig', [
            'massages' => $massageRepository->findAll(),
        ]);
    }

    #[Route('/{id}', name: 'app_massage_show', methods: ['GET'])]
    public function show(Care $massage): Response
    {
        return $this->render('massage/show.html.twig', [
            'massage' => $massage,
        ]);
    }

}
