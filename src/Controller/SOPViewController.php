<?php

namespace App\Controller;

use App\Entity\SOP;
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
        if ($request->isMethod('POST')) {
            $sop = new SOP();
            $sop->setTitle($request->request->get('title'));
            $sop->setDescription($request->request->get('description'));
            $sop->setDepartment($request->request->get('department'));
            $sop->setDifficulty((int) $request->request->get('difficulty', 1));
            $sop->setStatus('draft');
            $sop->setVersionNumber(1);
            $sop->setCreatedAt(new \DateTimeImmutable());
            $sop->setCreatedBy($this->getUser());

            $categoryId = $request->request->get('category');
            if ($categoryId) {
                $category = $this->categoryRepository->find($categoryId);
                if ($category) {
                    $sop->setCategory($category);
                }
            }

            $this->entityManager->persist($sop);
            $this->entityManager->flush();

            $this->addFlash('success', 'SOP created successfully!');
            return $this->redirectToRoute('app_sops_show', ['id' => $sop->getId()]);
        }

        return $this->render('sop/form.html.twig', [
            'sop' => null,
            'categories' => $this->categoryRepository->findAll(),
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

        if ($request->isMethod('POST')) {
            $sop->setTitle($request->request->get('title'));
            $sop->setDescription($request->request->get('description'));
            $sop->setDepartment($request->request->get('department'));
            $sop->setDifficulty((int) $request->request->get('difficulty', 1));
            $sop->setUpdatedAt(new \DateTimeImmutable());

            $categoryId = $request->request->get('category');
            if ($categoryId) {
                $category = $this->categoryRepository->find($categoryId);
                if ($category) {
                    $sop->setCategory($category);
                }
            }

            $this->entityManager->flush();

            $this->addFlash('success', 'SOP updated successfully!');
            return $this->redirectToRoute('app_sops_show', ['id' => $sop->getId()]);
        }

        return $this->render('sop/form.html.twig', [
            'sop' => $sop,
            'categories' => $this->categoryRepository->findAll(),
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
