<?php

namespace App\Controller;

use App\Repository\SOPRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/sops')]
final class SOPViewController extends AbstractController
{
    public function __construct(
        private SOPRepository $sopRepository
    ) {}

    #[Route('', name: 'app_sops_index', methods: ['GET'])]
    public function index(): Response
    {
        // For now, fetching all. Pagination can be added later.
        $sops = $this->sopRepository->findBy([], ['createdAt' => 'DESC']);

        return $this->render('sop/index.html.twig', [
            'sops' => $sops,
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
}
