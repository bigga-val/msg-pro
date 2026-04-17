<?php

namespace App\Controller;

use App\Repository\ContactGroupeRepository;
use App\Repository\ContactRepository;
use App\Repository\GroupeRepository;
use App\Repository\HistoriqueRepository;
use App\Repository\OrganisationRepository;
use App\Repository\TemplatesmsRepository;
use App\Entity\Templatesms;
use App\Repository\UserRepository;
use App\Service\EmailService;
use App\Service\SmsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/dashboard', name: 'app_home')]
    public function index(
        ContactRepository     $contactRepository,
        GroupeRepository      $groupeRepository,
        HistoriqueRepository  $historiqueRepository,
        OrganisationRepository $organisationRepository,
        SmsService            $smsService,
    ): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        if ($this->isGranted('ROLE_ADMIN')) {
            $historiques  = $historiqueRepository->findBy([], ['date' => 'DESC'], 5);
            $contacts     = $contactRepository->findContacts(true);
            $groupes      = $groupeRepository->findGroupes();
            $organisations = $organisationRepository->findAll();
        } else {
            $historiques  = $historiqueRepository->findBy(['user' => $this->getUser()], ['date' => 'DESC'], 5);
            $contacts     = $contactRepository->findContactsByUser($this->getUser()->getId());
            $groupes      = $groupeRepository->findGroupesByUser($this->getUser()->getId());
            $organisations = $organisationRepository->findBy(['user' => $this->getUser()]);
        }

        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
            'contacts'        => count($contacts),
            'groupes'         => count($groupes),
            'historiques'     => $historiques,
            'organisations'   => $organisations,
            'myBalance'       => $smsService->getBalance(),
        ]);
    }

    #[Route('/', name: 'app_landing')]
    public function landing(): Response
    {
        return $this->render('home/landing.html.twig', []);
    }

    #[Route('/contact-landing', name: 'app_landing_contact', methods: ['POST'])]
    public function landingContact(Request $request, EmailService $emailService): Response
    {
        $name    = $request->get('name', '');
        $email   = $request->get('email', '');
        $subject = $request->get('subject', '');
        $message = $request->get('message', '');

        $body = $emailService->contactMessageBody($name, $email, $subject, $message);
        $sent = $emailService->sendEmail('info@msg-pro.com', 'Contact : ' . $subject, $body);

        if ($sent) {
            $this->addFlash('success', 'Votre message a bien été envoyé. Nous vous répondrons dans les plus brefs délais.');
        } else {
            $this->addFlash('danger', 'Une erreur est survenue. Veuillez nous contacter directement par email ou WhatsApp.');
        }

        return $this->redirectToRoute('app_landing', ['#' => 'contact'], Response::HTTP_SEE_OTHER);
    }

    #[Route('/commander', name: 'app_commande')]
    public function commande(): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        return $this->render('home/commande.html.twig', []);
    }

    #[Route('/confirmercommande', name: 'app_confirmer_commande')]
    public function confirmercommande(Request $request): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        $prix      = $request->get('prix');
        $montant   = $request->get('montant');
        $prixFloat = floatval($prix);
        $total     = $prixFloat > 0 ? intval($montant) / $prixFloat : 0;

        return $this->render('home/confirmer.html.twig', [
            'prix'    => $prix,
            'montant' => $montant,
            'total'   => intval($total),
            'email'   => $this,
        ]);
    }

    #[Route('/envoi', name: 'app_envoi_sms')]
    public function envoi(
        OrganisationRepository $organisationRepository,
        GroupeRepository       $groupeRepository,
        TemplatesmsRepository  $templatesmsRepository,
    ): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        if ($this->isGranted('ROLE_ADMIN')) {
            $organisations = $organisationRepository->findAll();
            $templates     = $templatesmsRepository->findAll();
        } else {
            $organisations = $organisationRepository->findBy(['user' => $this->getUser()]);
            $templates     = $templatesmsRepository->findBy(['user' => $this->getUser()]);
        }

        return $this->render('home/envoi.html.twig', [
            'organisations' => $organisations,
            'templates'     => $templates,
        ]);
    }

    #[Route('/JsonListGroupsByOrganisation/{id}', name: 'JsonListGroupsByOrganisation', methods: ['GET'])]
    public function JsonListGroupsByOrganisation(
        Request                $request,
        GroupeRepository       $groupeRepository,
    ): JsonResponse
    {
        $groupes = $groupeRepository->findGroupesByOrganisation($request->get('id'));

        return new JsonResponse($groupes);
    }

    #[Route('/JsonSaveTemplate', name: 'JsonSaveTemplate', methods: ['GET'])]
    public function JsonSaveTemplate(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $templatesms = new Templatesms();
        $templatesms->setUser($this->getUser());
        $templatesms->setTexte($request->get('texte'));
        $templatesms->setTitre($request->get('titre'));
        $entityManager->persist($templatesms);
        $entityManager->flush();

        return new JsonResponse([$request->get('titre'), $request->get('texte')]);
    }

    #[Route('/unauthorized', name: 'app_unauthorized', methods: ['GET'])]
    public function unauthorized(): Response
    {
        return $this->render('home/unauthorized.html.twig', []);
    }

    #[Route('/EnvoiRapideSMS', name: 'EnvoiRapideSMS', methods: ['GET'])]
    public function EnvoiRapideSMS(
        Request                $request,
        EntityManagerInterface $entityManager,
        UserRepository         $userRepository,
        OrganisationRepository $organisationRepository,
        SmsService             $smsService,
    ): Response
    {
        $message = $request->get('message');
        $sender  = $request->get('expediteur');

        if ($sender != null && $sender != -1) {
            $org    = $organisationRepository->find($sender);
            $sender = ($org !== null && $org->isApproved()) ? $org->getDesignation() : 'insoft';
        } else {
            $sender = 'insoft';
        }

        $numero  = '%2b243' . substr($request->get('numero'), -9);
        $message = str_replace(' ', '+', $message);
        $user    = $userRepository->find($this->getUser()->getId());

        if (($user->getTotalSMS() ?? 0) <= 0) {
            return new JsonResponse("Vous n'avez pas assez de crédit.");
        }

        $result = $smsService->send($numero, $message, $sender);

        if (empty($result)) {
            return new JsonResponse("Erreur de communication avec l'opérateur SMS.");
        }

        $code   = $result['code'];
        $reason = $result['reason'];

        $smsService->logHistorique($user, $sender, $message, $numero, $code, $reason, $entityManager);

        if ($code == 0) {
            $smsService->deductCredit($user, $entityManager);
            return $this->redirectToRoute('app_home');
        }

        return new JsonResponse("Échec lors de l'envoi du SMS.");
    }

    #[Route('/JsonEnvoyerSMS', name: 'JsonEnvoyerSMS', methods: ['GET'])]
    public function JsonEnvoyerSMS(
        Request                  $request,
        EntityManagerInterface   $entityManager,
        OrganisationRepository   $organisationRepository,
        ContactGroupeRepository  $contactGroupeRepository,
        UserRepository           $userRepository,
        SmsService               $smsService,
    ): JsonResponse
    {
        $message      = $request->get('message');
        $organisation = $organisationRepository->find($request->get('expID'));
        $sender       = ($organisation !== null && $organisation->isApproved()) ? $organisation->getDesignation() : 'insoft';
        $groupeID     = $request->get('groupeID');

        $groupeContacts = $contactGroupeRepository->findBy(['groupe' => $groupeID]);
        $user           = $userRepository->find($this->getUser()->getId());
        $totalSMS       = $user->getTotalSMS() ?? 0;

        if (count($groupeContacts) === 0) {
            return new JsonResponse('Aucun contact trouvé dans ce groupe.');
        }

        if ($totalSMS <= 0 || count($groupeContacts) > $totalSMS) {
            return new JsonResponse('Crédit insuffisant. Vous avez ' . $totalSMS . ' SMS pour ' . count($groupeContacts) . ' contacts.');
        }

        $numbers      = '';
        $successCount = 0;

        foreach ($groupeContacts as $groupeContact) {
            try {
                $contact = $groupeContact->getContact();
                $tosend  = $smsService->interpolateMessage($message, $contact);
                $tosend  = str_replace(' ', '+', $tosend);
                $numero  = '%2b243' . substr($contact->getTelephone(), -9);
                $numbers .= $numero;

                $result = $smsService->send($numero, $tosend, $sender);

                if (empty($result)) {
                    continue;
                }

                $code   = $result['code'];
                $reason = $result['reason'] ?? '';

                $smsService->logHistoriqueOnly($user, $sender, $tosend, $numero, $code, $reason, $entityManager);

                if ($code == 0) {
                    $successCount++;
                }
            } catch (\Exception $e) {
                return new JsonResponse([false, $e->getMessage()]);
            }
        }

        // Déduction atomique en une seule transaction
        if ($successCount > 0) {
            $user->setTotalSMS(($user->getTotalSMS() ?? 0) - $successCount);
            $user->setUsedSMS(($user->getUsedSMS() ?? 0) + $successCount);
            $entityManager->persist($user);
        }
        $entityManager->flush();

        return new JsonResponse($numbers);
    }

    #[Route('/documentation', name: 'app_documentation', methods: ['GET'])]
    public function documentation(): Response
    {
        return $this->render('home/documentation.html.twig', []);
    }

    #[Route('/cgu', name: 'app_cgu', methods: ['GET'])]
    public function cgu(): Response
    {
        return $this->render('home/cgu.html.twig', []);
    }
}
