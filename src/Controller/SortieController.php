<?php

namespace App\Controller;

use App\Entity\Etat;
use App\Entity\Sortie;
use App\Form\SortieType;
use App\Repository\EtatRepository;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;



class SortieController extends AbstractController
{

    private $sortierepo;

 function __construct(SortieRepository $sortierepo){

     $this->sortierepo = $sortierepo;
 }

    /**
     * @Route("/sortie", name="app_sortie")
     */
    public function index(): Response
    {
        return $this->render('sortie/index.html.twig', [
            'controller_name' => 'SortieController',
        ]);
    }

    /**
     * @Route("/create", name="app_create")
     */
    public function create(Request $request, EntityManagerInterface $em, EtatRepository $etarepo): Response
    {

        $sortie  = new Sortie();

        $formSortie = $this->createForm(SortieType::class, $sortie);

        $formSortie->handleRequest($request);

      if($formSortie->isSubmitted() && $formSortie->isValid()){

          $etat = new Etat();

          $id = 1;

          $etat = $etarepo->find($id);

          $sortie->setEtat($etat);


          $em->persist($sortie);
          $em->flush();


        }


        return $this->render('sortie/index.html.twig', [
            'formSortie' => $formSortie->createView()
        ]);
    }
}
