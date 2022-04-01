<?php

namespace App\Controller;

use App\Entity\Site;
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
    public function liste(): Response
    {
        if(!$this->isGranted('ROLE_ADMIN')){
            return $this->redirectToRoute('app_liste_sortie');
        }
        $sites = $this->siteRepo->findAll();
        return $this->render('/site/liste_sites.html.twig', compact('sites'));
    }

    /**
     * @Route("create", name="create")
     */
    public function create(Request $request, EntityManagerInterface $em): Response
    {
        if(!$this->isGranted('ROLE_ADMIN')){
            return $this->redirectToRoute('app_liste_site');
        }
        $site = new Site();
        $formSite = $this->createForm(SiteType::class, $site);
        $formSite->handleRequest($request);

        if($formSite->isSubmitted() && $formSite->isValid()){
            $em->persist($site);
            $em->flush();
        }

        return $this->render('site/create.html.twig', [
            'formSite' => $formSite->createView()
        ]);
    }
}
