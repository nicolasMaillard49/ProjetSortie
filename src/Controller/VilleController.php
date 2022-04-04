<?php

namespace App\Controller;

use App\Entity\Lieu;
use App\Entity\Site;
use App\Entity\Ville;
use App\Form\LieuType;
use App\Form\ModifySiteFormType;
use App\Form\SiteType;
use App\Form\VilleType;
use App\Repository\LieuRepository;
use App\Repository\SiteRepository;
use App\Repository\VilleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/villes/", name="app_ville_")
 */
class VilleController extends AbstractController
{

    private $siteRepo;

    function __construct(VilleRepository $villeRepo)
    {
        $this->villeRepo = $villeRepo;
    }

    /**
     * @Route("liste", name="liste")
     */
    public function liste(EntityManagerInterface $em, Request $request): Response
    {
        if(!$this->isGranted('ROLE_USER')){
            return $this->redirectToRoute('app_liste_sortie');
        }
        $ville = new Ville();
        $formVille = $this->createForm(VilleType::class, $ville);
        $formVille->handleRequest($request);

        if($formVille->isSubmitted() && $formVille->isValid()){
            $em->persist($ville);
            $em->flush();
            return $this->redirectToRoute('app_ville_liste');
        }

        $villes = $this->villeRepo->findAll();
        return $this->render('/ville/liste_villes.html.twig',[
            'formVille' => $formVille->createView(),
            'villes' => $villes
        ]);
    }


    /**
     * @Route("supprimer", name="supprimer")
     */
    public function supprimer(Request $request): Response
    {
        if(!$this->isGranted('ROLE_ADMIN')){
            return $this->redirectToRoute('app_ville_liste');
        }
        $submittedToken = $request->request->get("token");

        if($this->isCsrfTokenValid('delete-item', $submittedToken)){
            $ville = $this->villeRepo->find($request->request->get("id"));
            $this->villeRepo->remove($ville);
        }

        return $this->json($this->isCsrfTokenValid('delete-item', $submittedToken));
    }
}
