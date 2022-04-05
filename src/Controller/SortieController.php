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
use DateTimeZone;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Exception;
use MongoDB\BSON\UTCDateTime;
use Psr\Container\ContainerInterface;
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
use function PHPUnit\Framework\stringContains;


class SortieController extends AbstractController
{

    private $sortierepo;
    private $particirepo;
    private $count;

    function __construct(SortieRepository $sortierepo)
    {

        $this->sortierepo = $sortierepo;
    }

    function __construct2(Participants $particirepo)
    {

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
    public function liste(Request $request, SiteRepository $siteRepo, EtatRepository $etatRepo, SortieRepository $sortieRepo)
    {

        if (!$this->getUser()) {
            return $this->render('security/login.html.twig');
        }
        //appel de la methode rechercheDetaillee dans SortieRepository afin de recupérer les sorties filtrées
        $sortiesQuery = new Sortie();
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

        //recuperation de tous les sites
        $sites = $siteRepo->findAll();
        //recuperation de tous les etats
        $etats = $etatRepo->findAll();


        $sorties = $sortiesQuery;

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

        $count = $sortie->getParticipants()->count();

        //dd($sortie);

        return $this->render('sortie/detail_sortie.html.twig', compact('sortie','count'));
    }

    /**
     * @Route("/create", name="app_create")
     */
    public function create(Request $request, EntityManagerInterface $em, EtatRepository $etarepo, ParticipantsRepository $partirepo): Response
    {

        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        $sortie = new Sortie();

        $formSortie = $this->createForm(SortieType::class, $sortie);

        $formSortie->handleRequest($request);


        if ($formSortie->isSubmitted() && $formSortie->isValid()) {

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
            return $this->redirectToRoute('app_detail_sortie', ['id' => $id]);


        }


        return $this->render('sortie/create_sortie.html.twig', [
            'formSortie' => $formSortie->createView()
        ]);

    }


    /**
     * @Route("/inscription/{sortie<\d+>}/participants/{participants_id<\d+>}", name="app_inscription")
     * @Entity (
     *     "participants",
     *     expr="repository.find(participants_id)"
     * )
     * @throws ORMException
     */
    public
    function inscription(Sortie $sortie, Participants $participants, EtatRepository $etatrepo, SortieRepository $sortierepo): Response
    {

        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }


        //on récupere l'id de la  sortie
        $id = $sortie->getId();

        //ici on instancie un nouvelle sortie NULL
        $sorties = new Sortie();

        //on lui passse en en parametres la sortie qu'ont récupere dans la fonction inscription
        $sorties = $sortierepo->find($id);

        //on instancie un nouvel etat
        $ouvert = new Etat();

        //un simple chiffre
        $id_etat_ouvert = 2;
        $id_etat_cloturer = 3;
        $id_etat_cour = 4;
        $id_etat_passe = 5;
        $id_etat_annule = 6;


        //ont vient récuppere l'etat via l'id
        $ouvert = $etatrepo->find($id_etat_ouvert);
        $cloture = $etatrepo->find($id_etat_cloturer);
        $cours = $etatrepo->find($id_etat_cour);
        $passe = $etatrepo->find($id_etat_passe);
        $annule = $etatrepo->find($id_etat_annule);

        //etat de la sortie actuellement
        $sortieEtat = $sortie->getEtat();


        //NOmbre max de participant
        $max = $sortie->getInscriptionMax();

        //comparaison avec la date du jour
        date_default_timezone_set('Europe/Paris');
        $datedebut = $sortie->getDateHeureDebut();
        $time = new \DateTime();
        $date = new \DateTime('@' . strtotime('now'));
        $time = date('H:i:s \O\n d/m/Y');


        //compteur de partticipant a la sortie
        $count = $sortie->getParticipants()->count();

        //date max inscription
        $dateLimite = $sortie->getDateLimiteInscription();

        //si l'etat de la sortie est oovert ont peut s'inscrire
        if ($sortieEtat === $ouvert) {

            if ($sortie->getParticipants() !== $sortie->getOrganisateur()) {

                if ($time < $dateLimite) {

                    if ($count < $max) {
                        $count = $count + 1;
                        $sortie->addParticipant($participants);
                        $sortierepo->add($sortie);
                        $this->addFlash('Success', "tu t'es bien inscrit a la sortie");
                        if($count == $max){
                            $sortie->setEtat($cloture);
                            $sortierepo->add($sortie);
                        }
                    } else {
                        $sortie->setEtat($cloture);
                        $sortierepo->add($sortie);
                        $this->addFlash('Failed', "la sortie a atteins sont Maximum d'inscription");
                    }

                } else {
                    $sortie->setEtat($cloture);
                    $sortierepo->add($sortie);
                    $this->addFlash('Failed', "la date limite d'inscription est passé man deso");
                }

            } else {
                $this->addFlash('Failed', "l'organiisateur est déja participant de cette sortie");
            }
        }else {
            $this->addFlash('Failed', "la sortie n'est pas ouverte");
             }



/*
        if ($sortie->getEtat() === $cloture) {
            $this->addFlash('Failed', "la sortie est cloturée");
        }

        if ($sortie->getEtat() === $cours) {
            $this->addFlash('Failed', "la sortie n'est pas ouverte");
        }

        if ($sortie->getEtat() === $passe) {
            $this->addFlash('Failed', "la sortie est passée");
        }

        if ($sortie->getEtat() === $annule) {
            $this->addFlash('Failed', "La sortie a été annulée");
        }*/


        return $this->render('sortie/detail_sortie.html.twig', compact('sortie','count'));
    }

    /**
     * @Route("date/{id}", name="app_date")
     */
    public function date($id, SortieRepository $sortierepo): Response
    {


        $sortie = new Site();

        $sortie = $sortierepo->find($id);

        $test = $sortie->getDateHeureDebut();

        $time = new \DateTime();
        $date = new \DateTime('@' . strtotime('now', DateTimeZone::UTC + 2));
        //$time = date('H:i:s \O\n d/m/Y');
        $time = date('Y-m-d H:i:s');


        if ($test > $time) {
            $result = 'date du jour superieru';
            dd($time, $result, $test);
        } else {
            $result = 'date du jour Inferieur';
            dd($time, $date, $result);

        }


    }


    /**
     * @Route("publier/{id<\d+>}", name="app_publier")
     */
    public function publier($id, SortieRepository $sortierepo, EtatRepository $etatrepo)
    {


        $sortie = $sortierepo->find($id);

        $id_sortie = $sortie->getId();

        $etat = new Etat();

        $id_etat = 2;

        $etat = $etatrepo->find($id_etat);

        $user = $this->getUser();

        $organisateur = $sortie->getOrganisateur();
        $count = $sortie->getParticipants()->count();


        if ($user === $organisateur) {
            $sortie->setEtat($etat);
            $sortierepo->add($sortie);
            $this->addFlash('Success', "Sortie bien publié");
        } else {
            $this->addFlash('Failed', "seul l'organisateur de la sortie peut la publier");
        }

        return $this->render('sortie/detail_sortie.html.twig', compact('sortie','count'));

    }


    /**
     * @Route("annule_sortie/{id<\d+>}", name="app_annule_sortie")
     */
    public function annule($id, SortieRepository $sortierepo, EtatRepository $etatrepo): Response
    {

        $sortie = $sortierepo->find($id);

        $id_sortie = $sortie->getId();

        $etat = new Etat();

        $id_etat = 6;

        $etat = $etatrepo->find($id_etat);

        $user = $this->getUser();

        $organisateur = $sortie->getOrganisateur();

        $count = $sortie->getParticipants()->count();

        date_default_timezone_set('Europe/Paris');
        $datedebut = $sortie->getDateHeureDebut();
        $time = new \DateTime();
        $date = new \DateTime('@' . strtotime('now'));
        $time = date('H:i:s \O\n d/m/Y');


        if ($user === $organisateur) {
            if ($datedebut >= $time) {
                $this->addFlash('Failed', "la date debut sortie est passé tu ne peut plus annuler la sortie");
            }
            else{
                $sortie->setEtat($etat);
                $sortierepo->add($sortie);
                $this->addFlash('Success', "Sortie Annuler avec succès");
            }
        } else {
            $this->addFlash('Failed', "seul l'organisateur peut annuler une sortie");
        }

        return $this->render('sortie/detail_sortie.html.twig', compact('sortie','count'));

    }

    /**
     * @Route("/desiste/{sortie<\d+>}/participants/{participants_id<\d+>}", name="app_desiste")
     * @Entity (
     *     "participants",
     *     expr="repository.find(participants_id)"
     * )
     * @throws ORMException
     */
    public function desiste(Sortie $sortie, Participants $participants,SortieRepository $sortierepo,EtatRepository $etatrepo): Response
    {

        $organisateur = $sortie->getOrganisateur();
        $count = $sortie->getParticipants()->count();
        $etat = new Etat();
        $etat = $etatrepo->find(2);
        $etatSor = $sortie->getId();
      if ($participants !== $organisateur and $etatSor == 2 or $etatSor == 3){
          $sortie->removeParticipant($participants);
          $sortie->setEtat($etat);
          $sortierepo->add($sortie);
          $this->addFlash('Success','tu ne fait plus partis des partiçipants de cette sortie');
      }else{
          $this->addFlash('Failed',"L'organisateur ne peut pas se désister d'une sortie");

      }

        return $this->render('sortie/detail_sortie.html.twig', compact('sortie','count'));
    }

}


