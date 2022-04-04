<?php

namespace App\Controller;

use App\Form\ModifyUserType;
use App\Repository\ParticipantsRepository;
use Doctrine\ORM\EntityManagerInterface;
use http\Message;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * @Route("/profil", name="app_profil_")
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

    /**
     * @Route("/liste_user", name="liste")
     */
    public function liste(Request $request): Response
    {
        if(!$this->isGranted('ROLE_ADMIN')){
            return $this->redirectToRoute('app_liste_sortie');
        }
        $participants = $this->participantsRepo->findAll();
        return $this->render('/profil/liste_user.html.twig', compact('participants'));
    }

    /**
     * @Route("/supprimer", name="supprimer")
     */
    public function supprimer(Request $request): Response
    {
        if(!$this->isGranted('ROLE_ADMIN')){
            return $this->redirectToRoute('app_liste_sortie');
        }
        $submittedToken = $request->request->get("token");

        if($this->isCsrfTokenValid('delete-item', $submittedToken)){
            $participant = $this->participantsRepo->find($request->request->get("id"));
            $this->participantsRepo->remove($participant);
        }

        return $this->json($this->isCsrfTokenValid('delete-item', $submittedToken));
    }


    /**
     * @Route("/modify_password", name="modify_password")
     */

    public function change_user_password(Request $request, UserPasswordEncoderInterface $encoder,EntityManagerInterface $em): Response
    {
            if(!$this->getUser()){
                return $this->redirectToRoute('app_liste_sortie');
            }
            if( $request->get('old_password') != null && $new_pwd = $request->get('new_password') && $new_pwd_confirm = $request->get('new_password_confirm')){
                $old_pwd = $request->get('old_password');
                $new_pwd = $request->get('new_password');
                $new_pwd_confirm = $request->get('new_password_confirm');

                $user = $this->getUser();

                $id = $user->getId();

                $participant =  $this->participantsRepo->find($id);
                $checkPass = $encoder->isPasswordValid($user, $old_pwd);

                if($checkPass === true) {
                    $new_pwd_encoded = $encoder->encodePassword($participant, $new_pwd_confirm);

                   $participant->setPassword($new_pwd_encoded);
                    $em->persist($participant);
                    $em->flush();

                    $this->addFlash('success', 'Mot de passe modifié avec succes');
                    return $this->redirectToRoute('app_logout');

                } else {
                    return new jsonresponse(array('error' => 'The current password is incorrect.'));
                }
}

            return $this->render('/profil/change_password.html.twig');
        }

}
