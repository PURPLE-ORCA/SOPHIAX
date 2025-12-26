<?php

namespace App\Controller;

use App\Entity\SOPStep;
use App\Repository\SOPStepRepository;
use App\Repository\SOPRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/sops/{sopId}/steps')]
final class SOPStepController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private SOPStepRepository $stepRepository,
        private SOPRepository $sopRepository
    ) {}

    #[Route('', name: 'sop_step_index', methods: ['GET'])]
    public function index(int $sopId): JsonResponse
    {
        $sop = $this->sopRepository->find($sopId);
        
        if (!$sop) {
            return $this->json(['error' => 'SOP not found'], 404);
        }

        $steps = $this->stepRepository->findBy(
            ['sop' => $sop], 
            ['stepNumber' => 'ASC']
        );
        
        return $this->json(array_map(fn(SOPStep $step) => [
            'id' => $step->getId(),
            'stepNumber' => $step->getStepNumber(),
            'content' => $step->getContent(),
            'attachment' => $step->getAttachment()
        ], $steps));
    }

    #[Route('/{id}', name: 'sop_step_show', methods: ['GET'])]
    public function show(int $sopId, int $id): JsonResponse
    {
        $step = $this->stepRepository->find($id);

        if (!$step || $step->getSop()?->getId() !== $sopId) {
            return $this->json(['error' => 'Step not found'], 404);
        }

        return $this->json([
            'id' => $step->getId(),
            'stepNumber' => $step->getStepNumber(),
            'content' => $step->getContent(),
            'attachment' => $step->getAttachment()
        ]);
    }

    #[Route('', name: 'sop_step_create', methods: ['POST'])]
    public function create(int $sopId, Request $request): JsonResponse
    {
        $sop = $this->sopRepository->find($sopId);
        
        if (!$sop) {
            return $this->json(['error' => 'SOP not found'], 404);
        }

        $data = json_decode($request->getContent(), true);

        if (!isset($data['content'])) {
            return $this->json(['error' => 'Content is required'], 400);
        }

        // Auto-calculate step number if not provided
        $stepNumber = $data['stepNumber'] ?? ($sop->getSteps()->count() + 1);

        $step = new SOPStep();
        $step->setSop($sop);
        $step->setStepNumber($stepNumber);
        $step->setContent($data['content']);
        $step->setAttachment($data['attachment'] ?? null);

        $this->entityManager->persist($step);
        
        // Update SOP updatedAt
        $sop->setUpdatedAt(new \DateTimeImmutable());
        
        $this->entityManager->flush();

        return $this->json([
            'message' => 'Step created successfully',
            'id' => $step->getId()
        ], 201);
    }

    #[Route('/{id}', name: 'sop_step_update', methods: ['PUT', 'PATCH'])]
    public function update(int $sopId, int $id, Request $request): JsonResponse
    {
        $step = $this->stepRepository->find($id);

        if (!$step || $step->getSop()?->getId() !== $sopId) {
            return $this->json(['error' => 'Step not found'], 404);
        }

        $data = json_decode($request->getContent(), true);

        if (isset($data['stepNumber'])) {
            $step->setStepNumber($data['stepNumber']);
        }
        if (isset($data['content'])) {
            $step->setContent($data['content']);
        }
        if (array_key_exists('attachment', $data)) {
            $step->setAttachment($data['attachment']);
        }

        // Update SOP updatedAt
        $step->getSop()?->setUpdatedAt(new \DateTimeImmutable());

        $this->entityManager->flush();

        return $this->json([
            'message' => 'Step updated successfully',
            'id' => $step->getId()
        ]);
    }

    #[Route('/{id}', name: 'sop_step_delete', methods: ['DELETE'])]
    public function delete(int $sopId, int $id): JsonResponse
    {
        $step = $this->stepRepository->find($id);

        if (!$step || $step->getSop()?->getId() !== $sopId) {
            return $this->json(['error' => 'Step not found'], 404);
        }

        $sop = $step->getSop();
        $deletedStepNumber = $step->getStepNumber();

        $this->entityManager->remove($step);

        // Reorder remaining steps
        if ($sop) {
            foreach ($sop->getSteps() as $remaining) {
                if ($remaining->getStepNumber() > $deletedStepNumber) {
                    $remaining->setStepNumber($remaining->getStepNumber() - 1);
                }
            }
            $sop->setUpdatedAt(new \DateTimeImmutable());
        }

        $this->entityManager->flush();

        return $this->json(['message' => 'Step deleted successfully']);
    }

    #[Route('/reorder', name: 'sop_step_reorder', methods: ['POST'])]
    public function reorder(int $sopId, Request $request): JsonResponse
    {
        $sop = $this->sopRepository->find($sopId);
        
        if (!$sop) {
            return $this->json(['error' => 'SOP not found'], 404);
        }

        $data = json_decode($request->getContent(), true);

        if (!isset($data['order']) || !is_array($data['order'])) {
            return $this->json(['error' => 'Order array is required'], 400);
        }

        // $data['order'] should be an array of step IDs in the new order
        $position = 1;
        foreach ($data['order'] as $stepId) {
            $step = $this->stepRepository->find($stepId);
            if ($step && $step->getSop()?->getId() === $sopId) {
                $step->setStepNumber($position);
                $position++;
            }
        }

        $sop->setUpdatedAt(new \DateTimeImmutable());
        $this->entityManager->flush();

        return $this->json(['message' => 'Steps reordered successfully']);
    }
}
