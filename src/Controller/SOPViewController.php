<?php

namespace App\Controller;

use App\Entity\SOP;
use App\Form\SOPType;
use App\Repository\SOPRepository;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/sops')]
final class SOPViewController extends AbstractController
{
    public function __construct(
        private SOPRepository $sopRepository,
        private CategoryRepository $categoryRepository,
        private EntityManagerInterface $entityManager
    ) {}

    #[Route('', name: 'app_sops_index', methods: ['GET'])]
    public function index(): Response
    {
        $sops = $this->sopRepository->findBy([], ['createdAt' => 'DESC']);

        return $this->render('sop/index.html.twig', [
            'sops' => $sops,
        ]);
    }

    #[Route('/new', name: 'app_sops_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $sop = new SOP();
        $sop->setDifficulty(1); // Default value
        
        $form = $this->createForm(SOPType::class, $sop);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $sop->setStatus('draft');
            $sop->setVersionNumber(1);
            $sop->setCreatedAt(new \DateTimeImmutable());
            $sop->setCreatedBy($this->getUser());

            $this->entityManager->persist($sop);
            $this->entityManager->flush();

            $this->addFlash('success', 'SOP created successfully!');
            return $this->redirectToRoute('app_sops_show', ['id' => $sop->getId()]);
        }

        return $this->render('sop/form.html.twig', [
            'form' => $form,
            'sop' => null,
        ]);
    }

    #[Route('/{id}', name: 'app_sops_show', methods: ['GET'])]
    public function show(int $id): Response
    {
        $sop = $this->sopRepository->find($id);

        if (!$sop) {
            throw $this->createNotFoundException('SOP not found');
        }

        return $this->render('sop/show.html.twig', [
            'sop' => $sop,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_sops_edit', methods: ['GET', 'POST'])]
    public function edit(int $id, Request $request): Response
    {
        $sop = $this->sopRepository->find($id);

        if (!$sop) {
            throw $this->createNotFoundException('SOP not found');
        }

        $form = $this->createForm(SOPType::class, $sop);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $sop->setUpdatedAt(new \DateTimeImmutable());

            $this->entityManager->flush();

            $this->addFlash('success', 'SOP updated successfully!');
            return $this->redirectToRoute('app_sops_show', ['id' => $sop->getId()]);
        }

        return $this->render('sop/form.html.twig', [
            'form' => $form,
            'sop' => $sop,
        ]);
    }

    #[Route('/{id}/delete', name: 'app_sops_delete', methods: ['POST'])]
    public function delete(int $id, Request $request): Response
    {
        $sop = $this->sopRepository->find($id);

        if (!$sop) {
            throw $this->createNotFoundException('SOP not found');
        }

        $this->entityManager->remove($sop);
        $this->entityManager->flush();

        $this->addFlash('success', 'SOP deleted successfully!');
        return $this->redirectToRoute('app_sops_index');
    }
}
