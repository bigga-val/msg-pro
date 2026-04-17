<?php

namespace App\Controller;
use App\Entity\Recharge;
use App\Repository\RechargeRepository;
use App\Repository\UserRepository;
use DateTimeZone;
use Doctrine\ORM\EntityManagerInterface;
use http\Client\Curl\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
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
        $userID   = $request->get('user');
        $quantite = $request->get('quantite');

        $user = $userRepository->find($userID);
        if ($user === null) {
            $this->addFlash('danger', 'Utilisateur introuvable.');
            return $this->redirectToRoute('app_liste_users');
        }

        $this->createRecharge(
            $this->getUser()->getUserIdentifier(),
            $this->getUser(),
            $user->getUsername(),
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

    public function createRecharge($user, $userID, $client, $clientID, $quantite, $balance,
    EntityManagerInterface $entityManager
    ):void{
        $recharge = new Recharge();
        $recharge->setUser($user);
        $recharge->setUtilisateur($userID);
        $recharge->setClient($client);
        $recharge->setClientid($clientID);
        $recharge->setQuantite($quantite);
        $recharge->setOldQuantite($balance);
        $recharge->setDate(new \datetime('now', new DateTimeZone('Africa/Kinshasa')));
        $entityManager->persist($recharge);
        $entityManager->flush();
    }

    #[Route(path:'/edit_profile', name: 'edit_profile')]
    public function edit_profile(Request $request, UserRepository $user, EntityManagerInterface $entityManager): Response
    {
        if($request->isMethod('POST')) {
//            dd($request->request->all());
            //dd($request->get('profile_token'));
            $myUser = $this->getUser();
            $myUser->setUsername($request->get('username'));
            $myUser->setEmail($request->get('email'));
            $myUser->setTelephone($request->get('telephone'));
            $entityManager->persist($myUser);
            $entityManager->flush();
            return $this->redirectToRoute('app_profile');
        }
        return $this->render('security/edit.html.twig');
    }



}
