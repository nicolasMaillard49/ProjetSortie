<?php

namespace App\Controller;

use App\Entity\Lieu;
use App\Entity\Site;
use App\Form\LieuType;
use App\Form\ModifySiteFormType;
use App\Form\SiteType;
use App\Repository\LieuRepository;
use App\Repository\SiteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/lieux/", name="app_lieu_")
 */
class LieuController extends AbstractController
{

    private $siteRepo;

    function __construct(LieuRepository $lieuRepo)
    {
        $this->lieuRepo = $lieuRepo;
    }

    /**
     * @Route("liste", name="liste")
     */
    public function liste(EntityManagerInterface $em, Request $request): Response
    {
        if(!$this->isGranted('ROLE_USER')){
            return $this->redirectToRoute('app_liste_sortie');
        }
        $lieu = new Lieu();
        $formLieu = $this->createForm(LieuType::class, $lieu);
        $formLieu->handleRequest($request);

        if($formLieu->isSubmitted() && $formLieu->isValid()){
            $em->persist($lieu);
            $em->flush();
            return $this->redirectToRoute('app_lieu_liste');
        }

        $lieux = $this->lieuRepo->findAll();
        return $this->render('/lieu/liste_lieux.html.twig',[
            'formLieu' => $formLieu->createView(),
            'lieux' => $lieux
        ]);
    }


    /**
     * @Route("supprimer", name="supprimer")
     */
    public function supprimer(Request $request): Response
    {
        if(!$this->isGranted('ROLE_ADMIN')){
            return $this->redirectToRoute('app_lieu_liste');
        }
        $submittedToken = $request->request->get("token");

        if($this->isCsrfTokenValid('delete-item', $submittedToken)){
            $lieu = $this->lieuRepo->find($request->request->get("id"));
            $this->lieuRepo->remove($lieu);
        }

        return $this->json($this->isCsrfTokenValid('delete-item', $submittedToken));
    }
}
