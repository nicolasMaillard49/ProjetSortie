<?php

namespace App\Controller;

use App\Entity\Etat;
use App\Entity\Participants;
use App\Entity\Site;
use App\Entity\Sortie;
use App\Form\FiltreType;
use App\Form\SortieType;
use App\Repository\EtatRepository;
use App\Repository\ParticipantsRepository;
use App\Repository\SiteRepository;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Form;
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
     * @throws Exception
     */
        public function liste(Request $request, SiteRepository $siteRepo, EtatRepository $etatRepo, SortieRepository $sortieRepo )
    {

        if( !$this->getUser()) {
            return $this->render('security/login.html.twig');
        }
        //appel de la methode rechercheDetaillee dans SortieRepository afin de recupérer les sorties filtrées

        $sortiesQuery = $sortieRepo->rechercheDetaillee(
            ($request->query->get('recherche_terme') != null ? $request->query->get('recherche_terme') : null),
            ($request->query->get('recherche_site') != null ? $request->query->get('recherche_site') : null),
            ($request->query->get('recherche_etat') != null ? $request->query->get('recherche_etat') : null),
            ($request->query->get('date_debut') != null ? $request->query->get('date_debut') : null),
            ($request->query->get('date_fin') != null ? $request->query->get('date_fin') : null),
            ($request->query->get('cb_organisateur') != null ? $request->query->get('cb_organisateur') : null),
            ($request->query->get('cb_inscrit') != null ? $request->query->get('cb_inscrit') : null),
            ($request->query->get('cb_non_inscrit') != null ? $request->query->get('cb_non_inscrit') : null),
            ($request->query->get('cb_passee') != null ? $request->query->get('cb_passee') : null)
        );

       /* //limitation à 10 sorties par page
        $sorties =  $sortiesQuery;
             $paginator->paginate(
            $sortiesQuery,
            $request->query->getInt('page', 1),
            10
        );*/

        //recuperation de tous les sites
        $sites = $siteRepo->findAll();
        //recuperation de tous les etats
        $etats = $etatRepo->findAll();

       // $sorties = $sortieRepo->findAll();

       /* if($sortiesQuery == null){
            $sorties = new Sortie();
            $sorties = $sortieRepo->findAll();
            $sortie = $sortiesQuery;
            dd($sorties,$sites,$etats,$sortie);
        }else{
            $sorties = new Sortie($sortiesQuery);
            dd($sorties,$sites,$etats);
        }*/

        $sorties = $sortiesQuery;



/*$sorties = $sortiesQuery;*/




        //délégation du travail au twig liste.html.twig en y passant en parametre les sorties filtrées, les sites et les etats
        return $this->render("sortie/liste_sorties.html.twig", [
            'sorties' => $sorties,
            'sites' => $sites,
            'etats' => $etats
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
     * @throws \Doctrine\ORM\ORMException
     */
    public function inscription(Sortie $sortie, Participants $participants): Response
    {


        $sortie->addParticipant($participants);

      $this->sortierepo->add($sortie);


       return $this->render('sortie/detail_sortie.html.twig',compact('sortie'));
    }

}
