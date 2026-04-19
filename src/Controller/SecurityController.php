<?php

namespace App\Controller;
use App\Entity\Recharge;
use App\Repository\ContactRepository;
use App\Repository\HistoriqueRepository;
use App\Repository\OrganisationRepository;
use App\Repository\RechargeRepository;
use App\Repository\UserRepository;
use DateTimeZone;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils, KernelInterface $kernel): Response
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        //dd(array_keys($this->container->getParameter('kernel.bundles')));
        //dd(array_keys($kernel->getBundles()));
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    #[Route(path:'/listeusers', name: 'app_liste_users')]
    public function listeusers(UserRepository $userRepository): Response
    {
        if(!$this->isGranted('ROLE_ADMIN')){
            throw $this->createAccessDeniedException();
        }
        $users = $userRepository->findAll();
        return $this->render('security/liste.html.twig', [
            'users'=> $users]
        );
    }

    #[Route(path: '/user/{id}', name: 'app_user_detail', methods: ['GET'])]
    public function userDetail(
        int $id,
        UserRepository $userRepository,
        HistoriqueRepository $historiqueRepository,
        RechargeRepository $rechargeRepository,
        OrganisationRepository $organisationRepository,
        ContactRepository $contactRepository,
    ): Response
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException();
        }

        $user = $userRepository->find($id);
        if ($user === null) {
            $this->addFlash('danger', 'Utilisateur introuvable.');
            return $this->redirectToRoute('app_liste_users');
        }

        return $this->render('security/user_detail.html.twig', [
            'user'          => $user,
            'historiques'   => $historiqueRepository->findBy(['user' => $user], ['date' => 'DESC'], 20),
            'recharges'     => $rechargeRepository->findBy(['clientid' => $user], ['date' => 'DESC']),
            'organisations' => $organisationRepository->findBy(['user' => $user]),
            'nbContacts'    => count($contactRepository->findContactsByUser($user->getId())),
        ]);
    }

    #[Route(path: '/toggle-user/{id}', name: 'app_toggle_user', methods: ['POST'])]
    public function toggleUser(int $id, Request $request, UserRepository $userRepository, EntityManagerInterface $entityManager): Response
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException();
        }

        $user = $userRepository->find($id);
        if ($user === null || !$this->isCsrfTokenValid('toggle_user_' . $id, $request->get('_token'))) {
            $this->addFlash('danger', 'Action non autorisée.');
            return $this->redirectToRoute('app_liste_users');
        }

        $user->setConfirmer(!$user->isConfirmer());
        $entityManager->flush();

        $status = $user->isConfirmer() ? 'activé' : 'désactivé';
        $this->addFlash('success', "Compte de {$user->getUsername()} {$status} avec succès.");

        return $this->redirectToRoute('app_liste_users');
    }

    #[Route(path:'/profile', name: 'app_profile')]
    public function profile(): Response
    {
        return $this->render('security/profile.html.twig');
    }

    #[Route(path:'/recharge', name: 'app_user_recharge', methods: ['POST'])]
    public function recharge(Request $request,
        UserRepository $userRepository,
    RechargeRepository $rechargeRepository,
    EntityManagerInterface $entityManager,
    ): Response
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException();
        }

        $userID   = $request->get('user');
        $quantite = $request->get('quantite');

        if (!$this->isCsrfTokenValid('recharge', $request->get('_csrf_token'))) {
            $this->addFlash('danger', 'Token de sécurité invalide.');
            return $this->redirectToRoute('app_liste_users');
        }

        $user = $userRepository->find($userID);
        if ($user === null) {
            $this->addFlash('danger', 'Utilisateur introuvable.');
            return $this->redirectToRoute('app_liste_users');
        }

        $this->createRecharge(
            $this->getUser(),
            $user,
            $quantite,
            $user->getTotalSMS(),
            $entityManager
        );

        $userqte = intval($user->getTotalSMS())  + intval($quantite);
        $user->setTotalSMS($userqte);
        $entityManager->persist($user);
        $entityManager->flush();

        //dd($userID, $quantite);
        return $this->redirectToRoute('app_liste_users');
    }

    #[Route(path:'/listerecharge', name: 'app_liste_recharges')]
    public function listerecharge(Request $request,
     RechargeRepository $rechargeRepository
    ): Response
    {
        $recharges = $rechargeRepository->findBy([], ['date' => 'DESC']);
        return $this->render('security/recharge.html.twig', [
            'recharges' => $recharges
        ]);

    }

    public function createRecharge($utilisateur, $client, $quantite, $balance,
    EntityManagerInterface $entityManager
    ):void{
        $recharge = new Recharge();
        $recharge->setUtilisateur($utilisateur);
        $recharge->setClientid($client);
        $recharge->setQuantite($quantite);
        $recharge->setOldQuantite($balance);
        $recharge->setDate(new \datetime('now', new DateTimeZone('Africa/Kinshasa')));
        $entityManager->persist($recharge);
        $entityManager->flush();
    }

    #[Route(path:'/edit_profile', name: 'edit_profile')]
    public function edit_profile(Request $request, UserRepository $user, EntityManagerInterface $entityManager, KernelInterface $kernel, SluggerInterface $slugger): Response
    {
        if ($request->isMethod('POST')) {
            $myUser = $this->getUser();
            $myUser->setUsername($request->get('username'));
            $myUser->setEmail($request->get('email'));
            $myUser->setTelephone($request->get('telephone'));

            $avatarFile = $request->files->get('avatar');
            if ($avatarFile !== null) {
                $allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
                if (!in_array($avatarFile->getMimeType(), $allowedTypes)) {
                    $this->addFlash('danger', 'Format de photo invalide. Acceptés : JPG, PNG, WEBP.');
                    return $this->redirectToRoute('edit_profile');
                }
                if ($avatarFile->getSize() > 2 * 1024 * 1024) {
                    $this->addFlash('danger', 'La photo ne doit pas dépasser 2 Mo.');
                    return $this->redirectToRoute('edit_profile');
                }

                $uploadsDir = $kernel->getProjectDir() . '/public/uploads/avatars';
                $originalFilename = pathinfo($avatarFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $avatarFile->guessExtension();

                // Supprimer l'ancienne photo
                if ($myUser->getAvatar()) {
                    $oldFile = $uploadsDir . '/' . $myUser->getAvatar();
                    if (file_exists($oldFile)) {
                        unlink($oldFile);
                    }
                }

                $avatarFile->move($uploadsDir, $newFilename);
                $myUser->setAvatar($newFilename);
            }

            $entityManager->flush();
            $this->addFlash('success', 'Profil mis à jour avec succès.');
            return $this->redirectToRoute('app_profile');
        }

        return $this->render('security/edit.html.twig');
    }



}
