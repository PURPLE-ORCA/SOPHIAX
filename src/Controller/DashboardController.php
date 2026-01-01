<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class DashboardController extends AbstractController
{
    #[Route('/', name: 'app_dashboard')]
    public function index(): Response
    {
        return $this->render('dashboard/index.html.twig', [
            'stats' => [
                'sops' => 24,
                'users' => 12,
                'categories' => 5,
                'pending_reviews' => 3
            ],
            'recent_sops' => [
                [
                    'id' => 1,
                    'title' => 'Safety Protocols 2024',
                    'category' => 'Safety',
                    'status' => 'published',
                    'updated_at' => new \DateTime('-2 days')
                ],
                [
                    'id' => 2,
                    'title' => 'onboarding Checklist (IT)',
                    'category' => 'HR',
                    'status' => 'draft',
                    'updated_at' => new \DateTime('-1 hour')
                ],
                [
                    'id' => 3,
                    'title' => 'Server Maintenance Guide',
                    'category' => 'IT',
                    'status' => 'published',
                    'updated_at' => new \DateTime('-1 week')
                ]
            ]
        ]);
    }
}
