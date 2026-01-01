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
                'total_sops' => 24,
                'total_users' => 12,
                'published_sops' => 18,
                'pending_reviews' => 3
            ],
            'recent_sops' => [
                [
                    'id' => 1,
                    'title' => 'Safety Protocols 2024',
                    'category' => 'Safety',
                    'status' => 'published',
                    'author' => 'Sarah Connor',
                    'updated_at' => new \DateTime('-2 days')
                ],
                [
                    'id' => 2,
                    'title' => 'Onboarding Checklist (IT)',
                    'category' => 'HR',
                    'status' => 'draft',
                    'author' => 'John Doe',
                    'updated_at' => new \DateTime('-1 hour')
                ],
                [
                    'id' => 3,
                    'title' => 'Server Maintenance Guide',
                    'category' => 'IT',
                    'status' => 'published',
                    'author' => 'Jane Smith',
                    'updated_at' => new \DateTime('-1 week')
                ],
                [
                    'id' => 4,
                    'title' => 'Q1 Marketing Strategy',
                    'category' => 'Marketing',
                    'status' => 'review',
                    'author' => 'Mike Ross',
                    'updated_at' => new \DateTime('-3 hours')
                ]
            ],
            'my_progress' => [
                ['title' => 'Fire Safety Basics', 'progress' => 100, 'status' => 'Completed'],
                ['title' => 'Data Privacy 101', 'progress' => 45, 'status' => 'In Progress'],
                ['title' => 'Company Values', 'progress' => 0, 'status' => 'Not Started'],
            ]
        ]);
    }
}
