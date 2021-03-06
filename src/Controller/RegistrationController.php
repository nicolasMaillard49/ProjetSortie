<?php

namespace App\Controller;

use App\Entity\Images;
use App\Entity\Participants;
use App\Form\RegistrationFormType;
use App\Repository\ImagesRepository;
use App\Repository\ParticipantsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class RegistrationController extends AbstractController
{
    /**
     * @Route("/register", name="app_register")
     * @IsGranted("ROLE_ADMIN")
     */
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager, ImagesRepository $imagerepo): Response
    {
        $user = new Participants();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);
        $user->setOrganisateur(0);
        $user->setActif(1);
        $roles[] = 'ROLE_USER';
        $user->setRoles($roles);

        if ($form->isSubmitted() && $form->isValid()) {
            //ont récupère l'image transmise
            $images = $form->get('images')->getData();

            if($images != null){
                if($user->getImages() != null){
                    $imageId = $user->getImages()->getId();
                    $image = $imagerepo->find($imageId);
                    $imagerepo->remove($image);
                }
                //ont génére un niuveau nom de fichieer aléatoire
                $fichier = md5(uniqid()). '.' .$images->guessExtension();
                //ont copie le nom du fechier dans le dossier upload
                $images->move(
                    $this->getParameter('images_directory'),
                    $fichier
                );
                //ont stocke le nom de l'image en bdd
                $img = new Images();
                $img->setName($fichier);
                $user->setImages($img);
            }
            // encode the plain password
            $user->setPassword(
            $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );
            $entityManager->persist($user);
            $entityManager->flush();
            return $this->redirectToRoute('app_liste_sortie');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
}
