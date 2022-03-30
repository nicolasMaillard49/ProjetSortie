<?php

namespace App\Controller;

use App\Entity\Etat;
use App\Entity\Participants;
use App\Entity\Sortie;
use App\Form\SortieType;
use App\Repository\EtatRepository;
use App\Repository\ParticipantsRepository;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;




class SortieController extends AbstractController
{

    private $sortierepo;
    private $particirepo;

 function __construct(SortieRepository $sortierepo){

     $this->sortierepo = $sortierepo;
 }

 function __construct2(Participants $particirepo){

     $this->particirepo = $particirepo;
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
     * @IsGranted("ROLE_USER")
     */
    public function listage(): Response
    {
        if( !$this->getUser()) {
            return $this->render('security/login.html.twig');
        }
            $sortie = new Sortie();

            $sortie = $this->sortierepo->findAll();

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



     return $this->render('sortie/detail_sortie.html.twig',compact('sortie'));
    }

   /**
     * @Route("/create", name="app_create")
    * @IsGranted("ROLE_ADMIN")
     */
    public function create(Request $request, EntityManagerInterface $em, EtatRepository $etarepo,ParticipantsRepository $partirepo): Response
    {

        $sortie  = new Sortie();

        $formSortie = $this->createForm(SortieType::class, $sortie);

        $formSortie->handleRequest($request);




      if($formSortie->isSubmitted() && $formSortie->isValid()){

          $etat = new Etat();

          $id = 1;

          $etat = $etarepo->find($id);

          $sortie->setEtat($etat);

          $organisateur = new Participants();

          $id = $this->getUser();

          $organisateur = $partirepo->find($id);

          $sortie->setOrganisateur($organisateur);

          $em->persist($sortie);
          $em->flush();

          $id = $sortie->getID();
          return $this->redirectToRoute('app_detail_sortie',['id'=>$id]);


        }

        return $this->render('sortie/create_sortie.html.twig', [
            'formSortie' => $formSortie->createView()
        ]);

    }


    /**
     * @Route("/inscription/{sortie<\d+>}/participants/{participants_id<\d+>}", name="app_inscription")
     *@Entity (
     *     "participants",
     *     expr="repository.find(participants_id)"
     * )
     * @IsGranted("ROLE_ADMIN")
     * @throws \Doctrine\ORM\ORMException
     */
    public function inscription(Sortie $sortie, Participants $participants): Response
    {


        $sortie->addParticipant($participants);

      $this->sortierepo->add($sortie);


       return $this->render('sortie/detail_sortie.html.twig',compact('sortie'));
    }

}
