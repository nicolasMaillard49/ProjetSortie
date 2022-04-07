<?php

namespace App\Controller;

use App\Entity\Images;
use App\Entity\Participants;
use App\Form\ModifyUserType;
use App\Repository\ImagesRepository;
use App\Repository\ParticipantsRepository;
use Doctrine\ORM\EntityManagerInterface;
use http\Message;
use phpDocumentor\Reflection\Types\True_;
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
    public function modifier(Request $request, EntityManagerInterface $em, $id, ImagesRepository $imagerepo): Response
    {
        //vérification si l'utilisateur existe et si l'utilisateur qui veut accéder à la page est bien celui connecté
        if(!$this->getUser()){
            return $this->redirectToRoute('app_login');
        }
        if($this->getUser()->getId()!=$id){
            if(!$this->isGranted('ROLE_ADMIN')){
                return $this->redirectToRoute('app_liste_sortie');
            }
        }
        //récupération de l'utilisateur dont on souhaite modifier les informations
        $user = $this->participantsRepo->find($id);
        $modifyUserForm = $this->createForm(ModifyUserType::class, $user);
        $modifyUserForm->handleRequest($request);
        if($modifyUserForm->isSubmitted() && $modifyUserForm->isValid()){
            if($modifyUserForm['actif']->getData() === false){
                $user->setActif(0);
                $roles[] = 'ROLE_INACTIF';
                $user->setRoles($roles);
            }
             $images = $modifyUserForm->get('images')->getData();
            if($images != null){
                if($user->getImages() != null){
                    $imageId = $user->getImages()->getId();
                    $image = $imagerepo->find($imageId);
                    $imagerepo->remove($image);
                }
                //on genère un nouveau nom de fichier aléatoire
                $fichier = md5(uniqid()). '.' .$images->guessExtension();
                //on copie le nom du fichier dans le dossier upload
                $images->move(
                    $this->getParameter('images_directory'),
                    $fichier
                );
                //on stock le nom de l'image en bdd
                $img = new Images();
                $img->setName($fichier);
                $user->setImages($img);
            }
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
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_liste_sortie');
        }

        if($request->get('new_password') != $request->get('new_password_confirm')){
            $this->addFlash('Failed', "Veuillez entrer des champs valides");
            return $this->redirectToRoute('app_profil_modify_password');
        }

        if ($request->get('old_password') != null && $request->get('new_password') != null && $request->get('new_password_confirm')
            != null && $request->get('new_password') == $request->get('new_password_confirm')) {



            $old_pwd = $request->get('old_password');
            $new_pwd = $request->get('new_password');
            $new_pwd_confirm = $request->get('new_password_confirm');

            $user = $this->getUser();

            $id = $user->getId();

            $participant = $this->participantsRepo->find($id);
            $checkPass = $encoder->isPasswordValid($user, $old_pwd);

            if ($checkPass === true) {
                $new_pwd_encoded = $encoder->encodePassword($participant, $new_pwd_confirm);

                $participant->setPassword($new_pwd_encoded);
                $em->persist($participant);
                $em->flush();

                $this->addFlash('success', 'Mot de passe modifié avec succes');
                return $this->redirectToRoute('app_logout');

            } else {
                $this->addFlash('Failed', 'Veuillez entrer des champs valides');
                return $this->redirectToRoute('app_profil_modify_password');
            }
        } else {
            return $this->render('profil/change_password.html.twig');
        }

    }

    /**
     * @Route("/verify_mail", name="verify_mail")
     */

    public function verify_mail(Request $request, ParticipantsRepository $prepo): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_liste_sortie');
        }
        $user = new Participants();
if($request->get('email') != null){
    $email = $request->get('email');
    $user = $prepo->findOneBy(array
    ('email'=>$email)
    );

    if($user != null){
        $id = $user->getId();
        return $this->redirectToRoute('app_profil_new_password',compact('id'));
    }else{

        $this->addFlash('Failed','aucun utilisateur trouver avec cette email');
        return $this->render('profil/verify_mail.html.twig' );
    }

    }
        return $this->render('profil/verify_mail.html.twig');
    }




    /**
     * @Route("/new_password/{id}", name="new_password")
     */
    public function new_password(Request $request, UserPasswordEncoderInterface $encoder,EntityManagerInterface $em,$id, ParticipantsRepository $prepo): Response
    {
        $user = new Participants();
        $user = $prepo->find($id);

        if ($this->getUser()) {
            return $this->redirectToRoute('app_liste_sortie');
        }

        if($request->get('new_password') != $request->get('new_password_confirm')){
            $this->addFlash('Failed', "Veuillez entrer des champs valides");

        }

        if ($request->get('new_password') != null && $request->get('new_password_confirm')
            != null && $request->get('new_password') == $request->get('new_password_confirm')) {

            $new_pwd = $request->get('new_password');
            $new_pwd_confirm = $request->get('new_password_confirm');


            $user = new Participants();

            $user = $prepo->find($id);

            $id = $user->getId();


                $new_pwd_encoded = $encoder->encodePassword($user, $new_pwd_confirm);

                $user->setPassword($new_pwd_encoded);
                $em->persist($user);
                $em->flush();

                $this->addFlash('success', 'Mot de passe modifié avec succes');
                return $this->redirectToRoute('app_logout');

        } else {
            return $this->render('profil/newPassword.html.twig',compact('user'));
        }


    }





    /**
     * @Route("/supprimer/image{id<\d+>}", name="supprimer_image")
     */
    public function supprimage(Request $request, ImagesRepository $imagerepo): Response
    {
        if(!$this->getUser()){
            return $this->redirectToRoute('app_liste_sortie');
        }

        $data = json_decode($request->getContent(),true);

        $submittedToken = $request->request->get("token");
        if($this->isCsrfTokenValid('delete-item', $submittedToken)){
            $image = $imagerepo->find($request->request->get("id"));
            $nom = $image->getName();
            unlink($this->getParameter('images_directory').'/'.$nom);

            $imagerepo->remove($image);
        }

        return $this->json($this->isCsrfTokenValid('delete-item', $submittedToken));
    }

}
