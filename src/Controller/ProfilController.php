<?php

namespace App\Controller;

use App\Repository\ParticipantsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/profil", name="app_profil_")
 */
class ProfilController extends AbstractController
{

    private $participantsRepo;

    function __construct(ParticipantsRepository $participantsRepo)
    {
        $this->participantsRepo = $participantsRepo;
    }


    /**
     * @Route("/{id}", name="profil")
     */
    public function detail($id): Response
    {
        if($this->getUser()){
            return $this->redirectToRoute('app_login');
        }
        $participants = $this->participantsRepo->find($id);
        return $this->render('profil/index.html.twig', compact("participants"));
    }
}
