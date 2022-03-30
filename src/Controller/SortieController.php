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
     * @Route("/sortie_listage", name="app_liste_sortie")
     */
    public function listage(): Response
    {
      $sortie = new Sortie();

      $sortie = $this->sortierepo->findAll();


/*      dd($sorti,$sortie,$nom);*/

     return $this->render('sortie/liste_sorties.html.twig',[
            'sorties'=>$sortie
        ]);
    }


    /**
     * @Route("/detail_sortie/{id}", name="app_detail_sortie")
     */
    public function detail($id): Response
    {
      $sortie = new Sortie();

      $sortie = $this->sortierepo->find($id);


        /*  dd($sortie);*/

     return $this->render('sortie/detail_sortie.html.twig',compact('sortie'));
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

        return $this->render('sortie/create_sortie.html.twig', [
            'formSortie' => $formSortie->createView()
        ]);


      function __tostring(Sortie $sortie):String
      {
          return $sortie;
      }




    }


}
