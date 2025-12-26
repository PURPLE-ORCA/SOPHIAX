<?php

namespace App\Controller;

use App\Entity\UserProgress;
use App\Repository\UserProgressRepository;
use App\Repository\UserRepository;
use App\Repository\SOPRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/progress')]
final class UserProgressController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserProgressRepository $progressRepository,
        private UserRepository $userRepository,
        private SOPRepository $sopRepository
    ) {}

    #[Route('/user/{userId}', name: 'progress_by_user', methods: ['GET'])]
    public function byUser(int $userId): JsonResponse
    {
        $user = $this->userRepository->find($userId);
        
        if (!$user) {
            return $this->json(['error' => 'User not found'], 404);
        }

        $progress = $this->progressRepository->findBy(['owner' => $user]);
        
        $stats = [
            'total' => count($progress),
            'notStarted' => 0,
            'inProgress' => 0,
            'completed' => 0
        ];

        $items = array_map(function(UserProgress $p) use (&$stats) {
            match ($p->getStatus()) {
                'not_started' => $stats['notStarted']++,
                'in_progress' => $stats['inProgress']++,
                'completed' => $stats['completed']++,
                default => null
            };

            return [
                'id' => $p->getId(),
                'status' => $p->getStatus(),
                'completedAt' => $p->getCompletedAt()?->format('c'),
                'sop' => $p->getSop() ? [
                    'id' => $p->getSop()->getId(),
                    'title' => $p->getSop()->getTitle(),
                    'difficulty' => $p->getSop()->getDifficulty()
                ] : null
            ];
        }, $progress);

        return $this->json([
            'user' => [
                'id' => $user->getId(),
                'name' => $user->getName()
            ],
            'stats' => $stats,
            'completionRate' => $stats['total'] > 0 
                ? round(($stats['completed'] / $stats['total']) * 100, 1) 
                : 0,
            'progress' => $items
        ]);
    }

    #[Route('/sop/{sopId}', name: 'progress_by_sop', methods: ['GET'])]
    public function bySop(int $sopId): JsonResponse
    {
        $sop = $this->sopRepository->find($sopId);
        
        if (!$sop) {
            return $this->json(['error' => 'SOP not found'], 404);
        }

        $progress = $this->progressRepository->findBy(['sop' => $sop]);
        
        $stats = [
            'total' => count($progress),
            'notStarted' => 0,
            'inProgress' => 0,
            'completed' => 0
        ];

        $items = array_map(function(UserProgress $p) use (&$stats) {
            match ($p->getStatus()) {
                'not_started' => $stats['notStarted']++,
                'in_progress' => $stats['inProgress']++,
                'completed' => $stats['completed']++,
                default => null
            };

            return [
                'id' => $p->getId(),
                'status' => $p->getStatus(),
                'completedAt' => $p->getCompletedAt()?->format('c'),
                'user' => $p->getOwner() ? [
                    'id' => $p->getOwner()->getId(),
                    'name' => $p->getOwner()->getName()
                ] : null
            ];
        }, $progress);

        return $this->json([
            'sop' => [
                'id' => $sop->getId(),
                'title' => $sop->getTitle()
            ],
            'stats' => $stats,
            'completionRate' => $stats['total'] > 0 
                ? round(($stats['completed'] / $stats['total']) * 100, 1) 
                : 0,
            'progress' => $items
        ]);
    }

    #[Route('', name: 'progress_create_or_update', methods: ['POST'])]
    public function createOrUpdate(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['userId']) || !isset($data['sopId'])) {
            return $this->json(['error' => 'userId and sopId are required'], 400);
        }

        $user = $this->userRepository->find($data['userId']);
        if (!$user) {
            return $this->json(['error' => 'User not found'], 404);
        }

        $sop = $this->sopRepository->find($data['sopId']);
        if (!$sop) {
            return $this->json(['error' => 'SOP not found'], 404);
        }

        // Check existing progress
        $progress = $this->progressRepository->findOneBy([
            'owner' => $user,
            'sop' => $sop
        ]);

        $isNew = false;
        if (!$progress) {
            $progress = new UserProgress();
            $progress->setOwner($user);
            $progress->setSop($sop);
            $isNew = true;
        }

        $status = $data['status'] ?? 'not_started';
        $progress->setStatus($status);

        if ($status === 'completed') {
            $progress->setCompletedAt(new \DateTimeImmutable());
        } else {
            $progress->setCompletedAt(null);
        }

        if ($isNew) {
            $this->entityManager->persist($progress);
        }

        $this->entityManager->flush();

        return $this->json([
            'message' => $isNew ? 'Progress created' : 'Progress updated',
            'id' => $progress->getId(),
            'status' => $progress->getStatus()
        ], $isNew ? 201 : 200);
    }

    #[Route('/start', name: 'progress_start', methods: ['POST'])]
    public function start(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $data['status'] = 'in_progress';
        
        $request = new Request([], [], [], [], [], [], json_encode($data));
        return $this->createOrUpdate($request);
    }

    #[Route('/complete', name: 'progress_complete', methods: ['POST'])]
    public function complete(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $data['status'] = 'completed';
        
        $request = new Request([], [], [], [], [], [], json_encode($data));
        return $this->createOrUpdate($request);
    }

    #[Route('/{id}', name: 'progress_delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $progress = $this->progressRepository->find($id);

        if (!$progress) {
            return $this->json(['error' => 'Progress not found'], 404);
        }

        $this->entityManager->remove($progress);
        $this->entityManager->flush();

        return $this->json(['message' => 'Progress deleted successfully']);
    }

    #[Route('/dashboard', name: 'progress_dashboard', methods: ['GET'])]
    public function dashboard(): JsonResponse
    {
        $allProgress = $this->progressRepository->findAll();
        
        $stats = [
            'totalUsers' => 0,
            'totalSops' => 0,
            'completedCount' => 0,
            'inProgressCount' => 0,
            'notStartedCount' => 0
        ];

        $userIds = [];
        $sopIds = [];

        foreach ($allProgress as $p) {
            if ($p->getOwner()) {
                $userIds[$p->getOwner()->getId()] = true;
            }
            if ($p->getSop()) {
                $sopIds[$p->getSop()->getId()] = true;
            }

            match ($p->getStatus()) {
                'not_started' => $stats['notStartedCount']++,
                'in_progress' => $stats['inProgressCount']++,
                'completed' => $stats['completedCount']++,
                default => null
            };
        }

        $stats['totalUsers'] = count($userIds);
        $stats['totalSops'] = count($sopIds);
        $stats['overallCompletionRate'] = count($allProgress) > 0 
            ? round(($stats['completedCount'] / count($allProgress)) * 100, 1) 
            : 0;

        return $this->json($stats);
    }
}
