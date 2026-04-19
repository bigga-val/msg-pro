<?php

namespace App\Controller;

use App\Entity\Groupe;
use App\Form\GroupeType;
use App\Repository\ContactGroupeRepository;
use App\Repository\ContactRepository;
use App\Repository\GroupeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use PhpOffice\PhpSpreadsheet\IOFactory;

#[Route('/groupe')]
final class GroupeController extends AbstractController
{
    #[Route(name: 'app_groupe_index', methods: ['GET'])]
    public function index(GroupeRepository $groupeRepository): Response
    {
        if(!$this->getUser()){
            return $this->redirectToRoute('app_login');
        }
        if($this->isGranted('ROLE_ADMIN')){
            $groupes = $groupeRepository->findGroupes();

        }else{
            $groupes = $groupeRepository->findGroupesByUser($this->getUser());

        }
        //dd($groupes);
        return $this->render('groupe/index.html.twig', [
            'groupes' => $groupes,
        ]);
    }

    #[Route('/new', name: 'app_groupe_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager,
        GroupeRepository $groupeRepository
    ): Response
    {
        if(!$this->getUser()){
            return $this->redirectToRoute('app_login');
        }

        if ($this->isGranted('ROLE_ADMIN')) {
            $groupes = $groupeRepository->findGroupes();
        } else {
            $groupes = $groupeRepository->findGroupesByUser($this->getUser());
        }

        return $this->render('groupe/new.html.twig', [
            'groupes' => $groupes,
        ]);
    }

    #[Route('/JsonSaveGroupe', name: 'json_groupe_new', methods: ['GET', 'POST'])]
    public function JsonSaveGroupe(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        if(!$this->getUser()){
            return $this->redirectToRoute('app_login');
        }
        $groupe = new Groupe();
        $groupe->setActive(true);
        $groupe->setDesignation($request->get('designation'));
        $groupe->setUser($this->getUser());
        $entityManager->persist($groupe);
        $entityManager->flush();

//            $entityManager->flush();
//        if ($form->isSubmitted() && $form->isValid()) {
//            $groupe->setActive(true);
//            $entityManager->persist($groupe);
//            $entityManager->flush();
//
//            return $this->redirectToRoute('app_groupe_index', [], Response::HTTP_SEE_OTHER);
//        }

        return new JsonResponse($groupe->getId());
    }

    #[Route('/{id}', name: 'app_groupe_show', methods: ['GET'])]
    public function show(Groupe $groupe, ContactGroupeRepository $contactGroupeRepository): Response
    {
        if(!$this->getUser()){
            return $this->redirectToRoute('app_login');
        }
        $contactGroupes = $contactGroupeRepository->findBy(['groupe' => $groupe]);
        return $this->render('groupe/show.html.twig', [
            'groupe' => $groupe,
            'contactGroupes' => $contactGroupes,
        ]);
    }

    #[Route('/attribuer/{id}', name: 'app_groupe_attribuer', methods: ['GET'])]
    public function attribuer(Groupe $groupe, ContactRepository $contactRepository,
        GroupeRepository $groupeRepository, ContactGroupeRepository $contactGroupeRepository
    ): Response
    {
        if(!$this->getUser()){
            return $this->redirectToRoute('app_login');
        }
        $contacts = $contactGroupeRepository->findBy(['groupe' => $groupe]);
        if($this->isGranted('ROLE_ADMIN')){
            $allcontacts = $groupeRepository->findContactNotInGroupe($groupe->getId());
        }else{
            $allcontacts = $groupeRepository->findContactNotInGroupeByUser($groupe->getId(), $this->getUser());
        }
        return $this->render('groupe/attribuer.html.twig', [
            'groupe' => $groupe,
            'contacts' => $contacts,
            'allcontacts'=> $allcontacts,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_groupe_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Groupe $groupe, EntityManagerInterface $entityManager): Response
    {
        if(!$this->getUser()){
            return $this->redirectToRoute('app_login');
        }
        $form = $this->createForm(GroupeType::class, $groupe);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_groupe_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('groupe/edit.html.twig', [
            'groupe' => $groupe,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_groupe_delete', methods: ['POST'])]
    public function delete(Request $request, Groupe $groupe, EntityManagerInterface $entityManager): Response
    {
        if(!$this->getUser()){
            return $this->redirectToRoute('app_login');
        }
        if ($this->isCsrfTokenValid('delete'.$groupe->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($groupe);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_groupe_index', [], Response::HTTP_SEE_OTHER);
    }


}
