<?php

namespace App\Controller;

use App\Entity\Templatesms;
use App\Form\TemplatesmsType;
use App\Repository\TemplatesmsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/templatesms')]
final class TemplatesmsController extends AbstractController
{
    #[Route(name: 'app_templatesms_index', methods: ['GET'])]
    public function index(TemplatesmsRepository $templatesmsRepository): Response
    {
        if($this->isGranted('ROLE_ADMIN')){
            $templatesms = $templatesmsRepository->findAll();
        }else{
            $templatesms = $templatesmsRepository->findBy(["user"=>$this->getUser()]);
        }
        return $this->render('templatesms/index.html.twig', [
            'templatesms' => $templatesms,
        ]);
    }

    #[Route('/new', name: 'app_templatesms_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $templatesm = new Templatesms();
        $form = $this->createForm(TemplatesmsType::class, $templatesm);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $templatesm->setUser($this->getUser());
            $entityManager->persist($templatesm);
            $entityManager->flush();

            return $this->redirectToRoute('app_templatesms_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('templatesms/new.html.twig', [
            'templatesm' => $templatesm,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_templatesms_show', methods: ['GET'])]
    public function show(Templatesms $templatesm): Response
    {
        return $this->render('templatesms/show.html.twig', [
            'templatesm' => $templatesm,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_templatesms_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Templatesms $templatesm, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(TemplatesmsType::class, $templatesm);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_templatesms_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('templatesms/edit.html.twig', [
            'templatesm' => $templatesm,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_templatesms_delete', methods: ['POST'])]
    public function delete(Request $request, Templatesms $templatesm, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$templatesm->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($templatesm);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_templatesms_index', [], Response::HTTP_SEE_OTHER);
    }
}
