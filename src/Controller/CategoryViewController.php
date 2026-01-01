<?php

namespace App\Controller;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/categories')]
final class CategoryViewController extends AbstractController
{
    public function __construct(
        private CategoryRepository $categoryRepository,
        private EntityManagerInterface $entityManager
    ) {}

    #[Route('', name: 'app_categories_index', methods: ['GET'])]
    public function index(): Response
    {
        $categories = $this->categoryRepository->findAll();

        return $this->render('category/index.html.twig', [
            'categories' => $categories,
        ]);
    }

    #[Route('/new', name: 'app_categories_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        if ($request->isMethod('POST')) {
            $category = new Category();
            $category->setName($request->request->get('name'));
            $category->setDescription($request->request->get('description'));

            $this->entityManager->persist($category);
            $this->entityManager->flush();

            $this->addFlash('success', 'Category created successfully!');
            return $this->redirectToRoute('app_categories_index');
        }

        return $this->render('category/form.html.twig', [
            'category' => null,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_categories_edit', methods: ['GET', 'POST'])]
    public function edit(int $id, Request $request): Response
    {
        $category = $this->categoryRepository->find($id);

        if (!$category) {
            throw $this->createNotFoundException('Category not found');
        }

        if ($request->isMethod('POST')) {
            $category->setName($request->request->get('name'));
            $category->setDescription($request->request->get('description'));

            $this->entityManager->flush();

            $this->addFlash('success', 'Category updated successfully!');
            return $this->redirectToRoute('app_categories_index');
        }

        return $this->render('category/form.html.twig', [
            'category' => $category,
        ]);
    }

    #[Route('/{id}/delete', name: 'app_categories_delete', methods: ['POST'])]
    public function delete(int $id): Response
    {
        $category = $this->categoryRepository->find($id);

        if (!$category) {
            throw $this->createNotFoundException('Category not found');
        }

        if ($category->getSops()->count() > 0) {
            $this->addFlash('error', 'Cannot delete category with existing SOPs.');
            return $this->redirectToRoute('app_categories_index');
        }

        $this->entityManager->remove($category);
        $this->entityManager->flush();

        $this->addFlash('success', 'Category deleted successfully!');
        return $this->redirectToRoute('app_categories_index');
    }
}
