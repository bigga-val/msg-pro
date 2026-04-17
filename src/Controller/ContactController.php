<?php

namespace App\Controller;

use App\Entity\Contact;
use App\Entity\ContactGroupe;
use App\Entity\Groupe;
use App\Form\ContactType;
use App\Repository\ContactGroupeRepository;
use App\Repository\ContactRepository;
use App\Repository\GroupeRepository;
use App\Repository\OrganisationRepository;
use App\Repository\VContactGroupeRepository;
use Doctrine\ORM\EntityManagerInterface;
use \Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/contact')]
final class ContactController extends AbstractController
{
    #[Route(name: 'app_contact_index', methods: ['GET'])]
    public function index(ContactRepository $contactRepository, VContactGroupeRepository $VContactGroupeRepository): Response
    {
        if(!$this->getUser()){
            return $this->redirectToRoute('app_login');
        }
        if($this->isGranted('ROLE_ADMIN')){
            $contacts = $contactRepository->findAll();

        }else{

           // $contacts = $contactRepository->findContactsByUserV2($this->getUser()->getId());
            $contacts = $VContactGroupeRepository->findBy(['user' => $this->getUser()], ['id' => 'DESC']);
        }
        //dd($contacts);
        return $this->render('contact/index.html.twig', [
            'contacts' => $contacts,
        ]);
    }

    #[Route('/new', name: 'app_contact_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, ContactGroupeRepository $contactGroupeRepository): Response
    {
        if(!$this->getUser()){
            return $this->redirectToRoute('app_login');
        }
        $contact = new Contact();
        $form = $this->createForm(ContactType::class, $contact);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $contact->setUser($this->getUser());
            $contact->setCreatedAt(new \DateTime());
            $entityManager->persist($contact);

            $selectedGroupe = $form->get('groupe')->getData();
            if ($selectedGroupe !== null) {
                $contactGroupe = new ContactGroupe();
                $contactGroupe->setContact($contact);
                $contactGroupe->setGroupe($selectedGroupe);
                $entityManager->persist($contactGroupe);
            }

            $entityManager->flush();

            return $this->redirectToRoute('app_contact_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('contact/new.html.twig', [
            'contact' => $contact,
            'form' => $form,
        ]);
    }

    #[Route('/JsonSaveContact', name: 'json_contact_new', methods: ['GET', 'POST'])]
    public function JsonSaveGroupe(Request $request, EntityManagerInterface $entityManager,
                                   GroupeRepository $groupeRepository
    ): JsonResponse
    {
        if(!$this->getUser()){
            return $this->redirectToRoute('app_login');
        }
        $contact = new Contact();
        $contact->setTelephone($request->get('telephone'));
        $contact->setNom($request->get('nom'));
        $contact->setPostnom($request->get('postnom'));
        $contact->setAdresse($request->get('adresse'));
        $contact->setFonction($request->get('fonction'));
        $contact->setUser($this->getUser());
        $contact->setCreatedAt(new \DateTime());

        $contactGroupe = new ContactGroupe();
        $contactGroupe->setContact($contact);
        $contactGroupe->setGroupe($groupeRepository->find($request->get('groupeID')));

        $entityManager->persist($contact);
        $entityManager->persist($contactGroupe);
        $entityManager->flush();
        return new JsonResponse(true);
    }


    #[Route('/JsonAttributeContact', name: 'json_contact_attribute', methods: ['GET', 'POST'])]
    public function JsonAttributeContact(Request $request, EntityManagerInterface $entityManager,
                                   GroupeRepository $groupeRepository, ContactRepository $contactRepository,
                                    ContactGroupeRepository $contactGroupeRepository
    ): JsonResponse
    {
        $contact = $contactRepository->find($request->query->get('contactID'));
        $groupe = $groupeRepository->find($request->query->get('groupeID'));
        $contactgroupe = new ContactGroupe();
        $contactgroupe->setContact($contact);
        $contactgroupe->setGroupe($groupe);

        $entityManager->persist($contactgroupe);
        $entityManager->flush();
        return new JsonResponse(true);
    }

    #[Route('/JsonDisallocateContact', name: 'json_contact_disallocate', methods: ['GET', 'POST'])]
    public function JsonDisallocateContact(Request $request, EntityManagerInterface $entityManager,
                                         GroupeRepository $groupeRepository, ContactRepository $contactRepository,
                                         ContactGroupeRepository $contactGroupeRepository
    ): JsonResponse
    {
        try {
            $contactgroupe = $contactGroupeRepository->find($request->query->get('contactID'));

            $entityManager->remove($contactgroupe);
            $entityManager->flush();
            return new JsonResponse(true);
        }catch (Exception $e){
            return new JsonResponse($e->getMessage());
        }

    }

    #[Route('/{id}', name: 'app_contact_show', methods: ['GET'])]
    public function show(Contact $contact): Response
    {
        if(!$this->getUser()){
            return $this->redirectToRoute('app_login');
        }
        return $this->render('contact/show.html.twig', [
            'contact' => $contact,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_contact_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Contact $contact, EntityManagerInterface $entityManager): Response
    {
        if(!$this->getUser()){
            return $this->redirectToRoute('app_login');
        }
        $form = $this->createForm(ContactType::class, $contact);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_contact_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('contact/edit.html.twig', [
            'contact' => $contact,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_contact_delete', methods: ['POST'])]
    public function delete(Request $request, Contact $contact, EntityManagerInterface $entityManager): Response
    {
        if(!$this->getUser()){
            return $this->redirectToRoute('app_login');
        }
        if ($this->isCsrfTokenValid('delete'.$contact->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($contact);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_contact_index', [], Response::HTTP_SEE_OTHER);
    }
}
