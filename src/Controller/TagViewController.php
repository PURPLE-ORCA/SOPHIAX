<?php

namespace App\Controller;

use App\Entity\Tag;
use App\Repository\TagRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/tags')]
final class TagViewController extends AbstractController
{
    public function __construct(
        private TagRepository $tagRepository,
        private EntityManagerInterface $entityManager
    ) {}

    #[Route('', name: 'app_tags_index', methods: ['GET'])]
    public function index(): Response
    {
        $tags = $this->tagRepository->findAll();

        return $this->render('tag/index.html.twig', [
            'tags' => $tags,
        ]);
    }

    #[Route('/new', name: 'app_tags_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        if ($request->isMethod('POST')) {
            $tag = new Tag();
            $tag->setName($request->request->get('name'));

            $this->entityManager->persist($tag);
            $this->entityManager->flush();

            $this->addFlash('success', 'Tag created successfully!');
            return $this->redirectToRoute('app_tags_index');
        }

        return $this->render('tag/form.html.twig', [
            'tag' => null,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_tags_edit', methods: ['GET', 'POST'])]
    public function edit(int $id, Request $request): Response
    {
        $tag = $this->tagRepository->find($id);

        if (!$tag) {
            throw $this->createNotFoundException('Tag not found');
        }

        if ($request->isMethod('POST')) {
            $tag->setName($request->request->get('name'));
            $this->entityManager->flush();

            $this->addFlash('success', 'Tag updated successfully!');
            return $this->redirectToRoute('app_tags_index');
        }

        return $this->render('tag/form.html.twig', [
            'tag' => $tag,
        ]);
    }

    #[Route('/{id}/delete', name: 'app_tags_delete', methods: ['POST'])]
    public function delete(int $id): Response
    {
        $tag = $this->tagRepository->find($id);

        if (!$tag) {
            throw $this->createNotFoundException('Tag not found');
        }

        $this->entityManager->remove($tag);
        $this->entityManager->flush();

        $this->addFlash('success', 'Tag deleted successfully!');
        return $this->redirectToRoute('app_tags_index');
    }
}
