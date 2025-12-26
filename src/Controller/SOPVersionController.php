<?php

namespace App\Controller;

use App\Entity\SOPVersion;
use App\Repository\SOPVersionRepository;
use App\Repository\SOPRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/sops/{sopId}/versions')]
final class SOPVersionController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private SOPVersionRepository $versionRepository,
        private SOPRepository $sopRepository
    ) {}

    #[Route('', name: 'sop_version_index', methods: ['GET'])]
    public function index(int $sopId): JsonResponse
    {
        $sop = $this->sopRepository->find($sopId);
        
        if (!$sop) {
            return $this->json(['error' => 'SOP not found'], 404);
        }

        $versions = $this->versionRepository->findBy(
            ['sop' => $sop], 
            ['versionNumber' => 'DESC']
        );
        
        return $this->json(array_map(fn(SOPVersion $version) => [
            'id' => $version->getId(),
            'versionNumber' => $version->getVersionNumber(),
            'createdAt' => $version->getCreatedAt()?->format('c'),
            'createdBy' => $version->getCreatedBy() ? [
                'id' => $version->getCreatedBy()->getId(),
                'name' => $version->getCreatedBy()->getName()
            ] : null
        ], $versions));
    }

    #[Route('/{id}', name: 'sop_version_show', methods: ['GET'])]
    public function show(int $sopId, int $id): JsonResponse
    {
        $version = $this->versionRepository->find($id);

        if (!$version || $version->getSop()?->getId() !== $sopId) {
            return $this->json(['error' => 'Version not found'], 404);
        }

        return $this->json([
            'id' => $version->getId(),
            'versionNumber' => $version->getVersionNumber(),
            'content' => $version->getContent(),
            'createdAt' => $version->getCreatedAt()?->format('c'),
            'createdBy' => $version->getCreatedBy() ? [
                'id' => $version->getCreatedBy()->getId(),
                'name' => $version->getCreatedBy()->getName()
            ] : null
        ]);
    }

    #[Route('/{id}/restore', name: 'sop_version_restore', methods: ['POST'])]
    public function restore(int $sopId, int $id): JsonResponse
    {
        $version = $this->versionRepository->find($id);

        if (!$version || $version->getSop()?->getId() !== $sopId) {
            return $this->json(['error' => 'Version not found'], 404);
        }

        $sop = $version->getSop();
        if (!$sop) {
            return $this->json(['error' => 'SOP not found'], 404);
        }

        // Create a snapshot of current state before restoring
        $currentSnapshot = new SOPVersion();
        $currentSnapshot->setSop($sop);
        $currentSnapshot->setVersionNumber($sop->getVersionNumber());
        $currentSnapshot->setCreatedAt(new \DateTimeImmutable());
        $currentSnapshot->setCreatedBy($sop->getCreatedBy());
        $currentSnapshot->setContent([
            'title' => $sop->getTitle(),
            'description' => $sop->getDescription(),
            'summary' => $sop->getSummary(),
            'difficulty' => $sop->getDifficulty(),
            'department' => $sop->getDepartment(),
            'status' => $sop->getStatus()
        ]);
        $this->entityManager->persist($currentSnapshot);

        // Restore the old version's content
        $content = $version->getContent();
        if (isset($content['title'])) {
            $sop->setTitle($content['title']);
        }
        if (isset($content['description'])) {
            $sop->setDescription($content['description']);
        }
        if (array_key_exists('summary', $content)) {
            $sop->setSummary($content['summary']);
        }
        if (isset($content['difficulty'])) {
            $sop->setDifficulty($content['difficulty']);
        }
        if (isset($content['department'])) {
            $sop->setDepartment($content['department']);
        }
        // Restoring keeps current status by default
        
        $sop->setVersionNumber($sop->getVersionNumber() + 1);
        $sop->setUpdatedAt(new \DateTimeImmutable());

        $this->entityManager->flush();

        return $this->json([
            'message' => 'SOP restored to version ' . $version->getVersionNumber(),
            'newVersionNumber' => $sop->getVersionNumber()
        ]);
    }

    #[Route('/compare/{version1}/{version2}', name: 'sop_version_compare', methods: ['GET'])]
    public function compare(int $sopId, int $version1, int $version2): JsonResponse
    {
        $v1 = $this->versionRepository->find($version1);
        $v2 = $this->versionRepository->find($version2);

        if (!$v1 || $v1->getSop()?->getId() !== $sopId) {
            return $this->json(['error' => 'Version 1 not found'], 404);
        }
        if (!$v2 || $v2->getSop()?->getId() !== $sopId) {
            return $this->json(['error' => 'Version 2 not found'], 404);
        }

        $content1 = $v1->getContent();
        $content2 = $v2->getContent();

        $diff = [];
        $allKeys = array_unique(array_merge(array_keys($content1), array_keys($content2)));

        foreach ($allKeys as $key) {
            $val1 = $content1[$key] ?? null;
            $val2 = $content2[$key] ?? null;
            
            if ($val1 !== $val2) {
                $diff[$key] = [
                    'version' . $v1->getVersionNumber() => $val1,
                    'version' . $v2->getVersionNumber() => $val2
                ];
            }
        }

        return $this->json([
            'version1' => [
                'id' => $v1->getId(),
                'versionNumber' => $v1->getVersionNumber(),
                'createdAt' => $v1->getCreatedAt()?->format('c')
            ],
            'version2' => [
                'id' => $v2->getId(),
                'versionNumber' => $v2->getVersionNumber(),
                'createdAt' => $v2->getCreatedAt()?->format('c')
            ],
            'differences' => $diff
        ]);
    }
}
