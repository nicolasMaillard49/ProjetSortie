<?php

namespace App\Controller;

use App\Entity\Site;
use App\Form\ModifySiteFormType;
use App\Form\SiteType;
use App\Repository\SiteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/sites/", name="app_site_")
 */
class SiteController extends AbstractController
{

    private $siteRepo;

    function __construct(SiteRepository $siteRepo)
    {
        $this->siteRepo = $siteRepo;
    }

    /**
     * @Route("liste", name="liste")
     */
    public function liste(EntityManagerInterface $em, Request $request): Response
    {
        if(!$this->isGranted('ROLE_ADMIN')){
            return $this->redirectToRoute('app_liste_sortie');
        }
        $site = new Site();
        $formSite = $this->createForm(SiteType::class, $site);
        $formSite->handleRequest($request);

        if($formSite->isSubmitted() && $formSite->isValid()){
            $em->persist($site);
            $em->flush();
            return $this->redirectToRoute('app_site_liste');
        }

        $sites = $this->siteRepo->findAll();
        return $this->render('/site/liste_sites.html.twig',[
            'formSite' => $formSite->createView(),
            'sites' => $sites
        ]);
    }


    /**
     * @Route("supprimer", name="supprimer")
     */
    public function supprimer(Request $request): Response
    {
        if(!$this->isGranted('ROLE_ADMIN')){
            return $this->redirectToRoute('app_site_liste');
        }
        $submittedToken = $request->request->get("token");

        if($this->isCsrfTokenValid('delete-item', $submittedToken)){
            $site = $this->siteRepo->find($request->request->get("id"));
            $this->siteRepo->remove($site);
        }

        return $this->json($this->isCsrfTokenValid('delete-item', $submittedToken));
    }
}
