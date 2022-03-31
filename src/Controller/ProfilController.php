<?php

namespace App\Controller;

use App\Form\ModifyUserType;
use App\Repository\ParticipantsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("profil", name="app_profil_")
 */
class ProfilController extends AbstractController
{

    private $participantsRepo;

    function __construct(ParticipantsRepository $participantsRepo)
    {
        $this->participantsRepo = $participantsRepo;
    }


    /**
     * @Route("/{id<\d+>}", name="id")
     */
    public function detail($id): Response
    {
        if(!$this->getUser()){
            return $this->redirectToRoute('app_login');
        }
        $participants = $this->participantsRepo->find($id);
        return $this->render('/profil/index.html.twig', compact("participants"));
    }

    /**
     * @Route("/modifier/{id<\d+>}", name="modifier_id")
     */
    public function modifier(Request $request, EntityManagerInterface $em, $id): Response
    {
        if(!$this->getUser()){
            return $this->redirectToRoute('app_login');
        }
        if($this->getUser()->getId()!=$id){
            if(!$this->isGranted('ROLE_ADMIN')){
                return $this->redirectToRoute('app_liste_sortie');
            }
        }

        $user = $this->participantsRepo->find($id);
        $modifyUserForm = $this->createForm(ModifyUserType::class, $user);
        $modifyUserForm->handleRequest($request);
        if($modifyUserForm->isSubmitted() && $modifyUserForm->isValid()){

            $em->persist($user);
            $em->flush();

            $this->addFlash('success', 'Profil modifié avec succès.');
            return $this->redirectToRoute('app_profil_id', ['id'=>$user->getId()]);
        }

        return $this->render('/profil/modify_user.html.twig', [
            'modifyUserForm' => $modifyUserForm->createView(),
            'participants' => $user
        ]);
    }










}
