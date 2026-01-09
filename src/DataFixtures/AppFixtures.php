<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\LearningPath;
use App\Entity\LearningPathItem;
use App\Entity\SOP;
use App\Entity\SOPStep;
use App\Entity\SOPVersion;
use App\Entity\Tag;
use App\Entity\User;
use App\Entity\UserProgress;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher
    ) {}

    public function load(ObjectManager $manager): void
    {
        // ===========================================
        // 1. CREATE USERS (10 users)
        // ===========================================
        $users = [];
        $userData = [
            ['name' => 'Admin User', 'email' => 'admin@sophiax.com', 'roles' => ['ROLE_ADMIN']],
            ['name' => 'Sarah Johnson', 'email' => 'sarah.johnson@sophiax.com', 'roles' => ['ROLE_EDITOR']],
            ['name' => 'Michael Chen', 'email' => 'michael.chen@sophiax.com', 'roles' => ['ROLE_EDITOR']],
            ['name' => 'Emily Rodriguez', 'email' => 'emily.rodriguez@sophiax.com', 'roles' => ['ROLE_EDITOR']],
            ['name' => 'David Kim', 'email' => 'david.kim@sophiax.com', 'roles' => ['ROLE_USER']],
            ['name' => 'Jessica Williams', 'email' => 'jessica.williams@sophiax.com', 'roles' => ['ROLE_USER']],
            ['name' => 'Robert Taylor', 'email' => 'robert.taylor@sophiax.com', 'roles' => ['ROLE_USER']],
            ['name' => 'Amanda Martinez', 'email' => 'amanda.martinez@sophiax.com', 'roles' => ['ROLE_USER']],
            ['name' => 'Chris Anderson', 'email' => 'chris.anderson@sophiax.com', 'roles' => ['ROLE_USER']],
            ['name' => 'Lisa Thompson', 'email' => 'lisa.thompson@sophiax.com', 'roles' => ['ROLE_USER']],
        ];

        foreach ($userData as $data) {
            $user = new User();
            $user->setName($data['name']);
            $user->setEmail($data['email']);
            $user->setRoles($data['roles']);
            $user->setPassword($this->passwordHasher->hashPassword($user, 'password123'));
            $user->setCreatedAt(new \DateTimeImmutable('-' . rand(1, 365) . ' days'));
            $manager->persist($user);
            $users[] = $user;
        }

        // ===========================================
        // 2. CREATE CATEGORIES (8 categories)
        // ===========================================
        $categories = [];
        $categoryData = [
            ['name' => 'IT & Technology', 'description' => 'Standard operating procedures for IT infrastructure, software development, and technical support operations.'],
            ['name' => 'Human Resources', 'description' => 'HR processes including recruitment, onboarding, performance management, and employee relations.'],
            ['name' => 'Finance & Accounting', 'description' => 'Financial procedures covering budgeting, expense management, invoicing, and compliance reporting.'],
            ['name' => 'Operations', 'description' => 'Day-to-day operational procedures for facilities, logistics, and general business operations.'],
            ['name' => 'Sales & Marketing', 'description' => 'Procedures for lead generation, client management, campaign execution, and sales processes.'],
            ['name' => 'Customer Support', 'description' => 'Customer service guidelines, ticket handling, escalation procedures, and satisfaction tracking.'],
            ['name' => 'Quality Assurance', 'description' => 'Quality control processes, testing procedures, and compliance standards documentation.'],
            ['name' => 'Security & Compliance', 'description' => 'Security protocols, data protection procedures, and regulatory compliance guidelines.'],
        ];

        foreach ($categoryData as $data) {
            $category = new Category();
            $category->setName($data['name']);
            $category->setDescription($data['description']);
            $manager->persist($category);
            $categories[$data['name']] = $category;
        }

        // ===========================================
        // 3. CREATE TAGS (20 tags)
        // ===========================================
        $tags = [];
        $tagNames = [
            'urgent', 'beginner', 'advanced', 'mandatory', 'optional',
            'remote-work', 'in-office', 'daily', 'weekly', 'monthly',
            'new-hire', 'manager-only', 'all-staff', 'compliance', 'safety',
            'customer-facing', 'internal', 'automation', 'manual', 'review-required'
        ];

        foreach ($tagNames as $tagName) {
            $tag = new Tag();
            $tag->setName($tagName);
            $manager->persist($tag);
            $tags[$tagName] = $tag;
        }

        // ===========================================
        // 4. CREATE SOPs (25 detailed SOPs)
        // ===========================================
        $sops = [];
        $sopData = [
            // IT & Technology SOPs
            [
                'title' => 'New Employee Workstation Setup',
                'description' => 'Complete guide for setting up a new employee workstation including hardware configuration, software installation, and network access provisioning.',
                'summary' => 'Step-by-step workstation setup for new hires covering hardware, software, and access.',
                'category' => 'IT & Technology',
                'department' => 'IT',
                'difficulty' => 2,
                'status' => 'published',
                'tags' => ['new-hire', 'mandatory', 'beginner'],
                'steps' => [
                    'Verify hardware inventory and unpack workstation components',
                    'Connect all cables and peripherals (monitor, keyboard, mouse)',
                    'Power on the system and complete initial Windows/macOS setup',
                    'Install company standard software suite (Office 365, Slack, Zoom)',
                    'Configure VPN client and verify remote access capability',
                    'Set up email client and calendar synchronization',
                    'Install security software and run initial scan',
                    'Configure printer access and test print functionality',
                    'Document workstation details in asset management system',
                    'Conduct brief orientation with the new employee'
                ]
            ],
            [
                'title' => 'Password Reset Procedure',
                'description' => 'Standard procedure for handling password reset requests from employees, including verification and security protocols.',
                'summary' => 'Secure password reset process with identity verification and audit logging.',
                'category' => 'IT & Technology',
                'department' => 'IT',
                'difficulty' => 1,
                'status' => 'published',
                'tags' => ['all-staff', 'daily', 'beginner'],
                'steps' => [
                    'Receive password reset request via ticket system or phone',
                    'Verify employee identity using security questions',
                    'Check employee status in HR system to confirm active employment',
                    'Generate temporary password using secure password generator',
                    'Send temporary password via approved secure channel',
                    'Log password reset activity in security audit system',
                    'Follow up to confirm successful password change'
                ]
            ],
            [
                'title' => 'Server Deployment Checklist',
                'description' => 'Comprehensive checklist for deploying new production servers including security hardening, monitoring setup, and documentation requirements.',
                'summary' => 'Production server deployment with security hardening and monitoring configuration.',
                'category' => 'IT & Technology',
                'department' => 'Infrastructure',
                'difficulty' => 4,
                'status' => 'published',
                'tags' => ['advanced', 'mandatory', 'compliance'],
                'steps' => [
                    'Complete server provisioning request form with resource requirements',
                    'Allocate and configure virtual machine or physical server',
                    'Apply base operating system security hardening template',
                    'Configure network settings and firewall rules',
                    'Install and configure monitoring agents (Datadog/Prometheus)',
                    'Set up log aggregation and forwarding',
                    'Configure backup schedule and verify restoration process',
                    'Apply appropriate SSL/TLS certificates',
                    'Document server details in infrastructure wiki',
                    'Obtain security team sign-off before production use',
                    'Add server to change management system',
                    'Schedule first maintenance window'
                ]
            ],
            [
                'title' => 'Software Development Code Review',
                'description' => 'Standards and procedures for conducting effective code reviews including checklist items and approval workflow.',
                'summary' => 'Code review best practices ensuring quality, security, and maintainability.',
                'category' => 'IT & Technology',
                'department' => 'Engineering',
                'difficulty' => 3,
                'status' => 'published',
                'tags' => ['mandatory', 'review-required', 'advanced'],
                'steps' => [
                    'Verify pull request includes proper description and ticket reference',
                    'Check code follows team style guide and naming conventions',
                    'Review logic for correctness and edge case handling',
                    'Verify appropriate test coverage exists',
                    'Check for security vulnerabilities and sensitive data exposure',
                    'Review database queries for performance implications',
                    'Verify documentation is updated if needed',
                    'Provide constructive feedback with specific suggestions',
                    'Approve or request changes with clear reasoning',
                    'Verify CI/CD pipeline passes all checks before merge'
                ]
            ],
            [
                'title' => 'Database Backup and Recovery',
                'description' => 'Procedures for database backup verification and disaster recovery testing for critical business systems.',
                'summary' => 'Database backup verification and DR testing procedures.',
                'category' => 'IT & Technology',
                'department' => 'Database',
                'difficulty' => 5,
                'status' => 'published',
                'tags' => ['compliance', 'mandatory', 'advanced'],
                'steps' => [
                    'Verify automated backup jobs completed successfully',
                    'Check backup file integrity using checksums',
                    'Test backup restoration in isolated environment',
                    'Validate data consistency post-restoration',
                    'Document recovery time and any issues encountered',
                    'Update recovery playbook if procedures changed',
                    'Report results to management and compliance team',
                    'Archive test results for audit purposes'
                ]
            ],

            // Human Resources SOPs
            [
                'title' => 'Employee Onboarding Process',
                'description' => 'Complete onboarding workflow for new employees from offer acceptance through first 90 days including documentation, training, and integration milestones.',
                'summary' => 'Comprehensive 90-day onboarding program for new hires.',
                'category' => 'Human Resources',
                'department' => 'HR',
                'difficulty' => 2,
                'status' => 'published',
                'tags' => ['new-hire', 'mandatory', 'beginner'],
                'steps' => [
                    'Send welcome email with first day instructions',
                    'Prepare new hire paperwork and benefits enrollment forms',
                    'Coordinate with IT for equipment and system access',
                    'Schedule orientation sessions and training programs',
                    'Assign buddy/mentor for first 30 days',
                    'Complete I-9 and tax documentation on first day',
                    'Conduct company culture and policies overview',
                    'Set up 30/60/90 day check-in meetings with manager',
                    'Ensure completion of mandatory compliance training',
                    'Collect new hire feedback at 30 and 90 days'
                ]
            ],
            [
                'title' => 'Performance Review Process',
                'description' => 'Annual and quarterly performance review procedures including self-assessment, manager evaluation, calibration, and feedback delivery.',
                'summary' => 'Structured performance review process with calibration and feedback.',
                'category' => 'Human Resources',
                'department' => 'HR',
                'difficulty' => 3,
                'status' => 'published',
                'tags' => ['manager-only', 'compliance', 'review-required'],
                'steps' => [
                    'Announce review cycle start date and deadlines',
                    'Distribute self-assessment forms to all employees',
                    'Managers complete evaluation forms for direct reports',
                    'Conduct calibration sessions within departments',
                    'Finalize ratings after cross-department calibration',
                    'Schedule one-on-one feedback meetings',
                    'Deliver performance feedback with specific examples',
                    'Document development goals and action items',
                    'Submit final reviews to HR system',
                    'Process any compensation adjustments'
                ]
            ],
            [
                'title' => 'Employee Termination Procedure',
                'description' => 'Standard procedure for voluntary and involuntary employee separations including system access revocation, exit interviews, and final pay processing.',
                'summary' => 'Secure employee separation process with access revocation and compliance.',
                'category' => 'Human Resources',
                'department' => 'HR',
                'difficulty' => 4,
                'status' => 'published',
                'tags' => ['compliance', 'mandatory', 'manager-only'],
                'steps' => [
                    'Receive resignation or termination decision documentation',
                    'Notify IT to schedule access revocation',
                    'Calculate final pay including unused PTO',
                    'Prepare separation agreement if applicable',
                    'Schedule exit interview with HR',
                    'Collect company property and equipment',
                    'Process system access termination on last day',
                    'Send COBRA and benefits continuation information',
                    'Archive employee records per retention policy',
                    'Complete separation checklist and close file'
                ]
            ],
            [
                'title' => 'Time Off Request Approval',
                'description' => 'Guidelines for submitting and approving vacation, sick leave, and personal time off requests.',
                'summary' => 'Time off request and approval workflow.',
                'category' => 'Human Resources',
                'department' => 'HR',
                'difficulty' => 1,
                'status' => 'published',
                'tags' => ['all-staff', 'daily', 'beginner'],
                'steps' => [
                    'Employee submits request through HR system',
                    'System checks available balance automatically',
                    'Manager receives notification for approval',
                    'Manager reviews team coverage and project timelines',
                    'Approve or deny with reason within 48 hours',
                    'Employee receives notification of decision',
                    'Approved time automatically added to team calendar'
                ]
            ],

            // Finance & Accounting SOPs
            [
                'title' => 'Invoice Processing Workflow',
                'description' => 'Standard procedure for receiving, verifying, coding, and processing vendor invoices for payment.',
                'summary' => 'End-to-end invoice processing from receipt to payment.',
                'category' => 'Finance & Accounting',
                'department' => 'Finance',
                'difficulty' => 2,
                'status' => 'published',
                'tags' => ['daily', 'mandatory', 'beginner'],
                'steps' => [
                    'Receive invoice via email or mail',
                    'Verify invoice matches purchase order',
                    'Check goods/services were received',
                    'Code invoice to appropriate GL accounts',
                    'Scan and attach supporting documentation',
                    'Route for departmental approval',
                    'Enter approved invoice into AP system',
                    'Schedule for payment per vendor terms',
                    'Archive digital copy of all documents'
                ]
            ],
            [
                'title' => 'Expense Report Submission',
                'description' => 'Guidelines for employees to submit business expense reports including required documentation and approval thresholds.',
                'summary' => 'Employee expense reporting with documentation requirements.',
                'category' => 'Finance & Accounting',
                'department' => 'Finance',
                'difficulty' => 1,
                'status' => 'published',
                'tags' => ['all-staff', 'weekly', 'beginner'],
                'steps' => [
                    'Collect all receipts and supporting documents',
                    'Log into expense management system',
                    'Create new expense report with purpose description',
                    'Enter each expense with category and business justification',
                    'Attach digital copies of receipts',
                    'Submit for manager approval',
                    'Respond to any clarification requests promptly',
                    'Reimbursement processed within 2 pay cycles'
                ]
            ],
            [
                'title' => 'Monthly Financial Close',
                'description' => 'Comprehensive checklist for month-end financial close process including journal entries, reconciliations, and reporting.',
                'summary' => 'Month-end close procedures with reconciliation and reporting.',
                'category' => 'Finance & Accounting',
                'department' => 'Finance',
                'difficulty' => 4,
                'status' => 'published',
                'tags' => ['monthly', 'mandatory', 'advanced'],
                'steps' => [
                    'Post all pending journal entries',
                    'Complete bank reconciliations',
                    'Reconcile intercompany accounts',
                    'Review and accrue unpaid expenses',
                    'Calculate depreciation and amortization',
                    'Review revenue recognition entries',
                    'Prepare trial balance',
                    'Generate financial statements',
                    'Complete variance analysis vs. budget',
                    'Present results to leadership',
                    'Archive all workpapers and documentation'
                ]
            ],
            [
                'title' => 'Budget Planning Process',
                'description' => 'Annual budget planning and approval workflow including departmental submissions and executive review.',
                'summary' => 'Annual budget cycle from planning to approval.',
                'category' => 'Finance & Accounting',
                'department' => 'Finance',
                'difficulty' => 5,
                'status' => 'draft',
                'tags' => ['manager-only', 'compliance', 'advanced'],
                'steps' => [
                    'Distribute budget templates to department heads',
                    'Provide prior year actuals and current trends',
                    'Set timeline for submission and review',
                    'Collect departmental budget requests',
                    'Consolidate into company-wide budget',
                    'Review with executive team',
                    'Conduct iterative revisions as needed',
                    'Present to board for approval',
                    'Communicate approved budgets to departments',
                    'Load budgets into financial system'
                ]
            ],

            // Operations SOPs
            [
                'title' => 'Facility Access Control',
                'description' => 'Procedures for managing building access including badge issuance, visitor management, and after-hours protocols.',
                'summary' => 'Physical security and access management procedures.',
                'category' => 'Operations',
                'department' => 'Facilities',
                'difficulty' => 2,
                'status' => 'published',
                'tags' => ['safety', 'mandatory', 'compliance'],
                'steps' => [
                    'New employees request badge through HR system',
                    'Verify employment status and clearance level',
                    'Capture badge photo per company standards',
                    'Program access permissions by role',
                    'Issue badge and document in tracking system',
                    'Conduct building safety orientation',
                    'Review badge access logs weekly',
                    'Deactivate badges immediately upon termination'
                ]
            ],
            [
                'title' => 'Equipment Maintenance Schedule',
                'description' => 'Preventive maintenance procedures for office equipment including HVAC, elevators, and common area appliances.',
                'summary' => 'Scheduled maintenance checklist for facility equipment.',
                'category' => 'Operations',
                'department' => 'Facilities',
                'difficulty' => 3,
                'status' => 'published',
                'tags' => ['weekly', 'mandatory', 'safety'],
                'steps' => [
                    'Review maintenance calendar for scheduled items',
                    'Verify vendor contracts and service agreements',
                    'Coordinate access with building management',
                    'Supervise vendor maintenance activities',
                    'Document completed work and any findings',
                    'Update equipment log with service dates',
                    'Schedule follow-up if repairs needed',
                    'File maintenance reports for compliance'
                ]
            ],
            [
                'title' => 'Emergency Evacuation Procedure',
                'description' => 'Building evacuation protocols including fire, severe weather, and other emergency situations.',
                'summary' => 'Emergency response and evacuation procedures.',
                'category' => 'Operations',
                'department' => 'Safety',
                'difficulty' => 1,
                'status' => 'published',
                'tags' => ['all-staff', 'mandatory', 'safety'],
                'steps' => [
                    'Sound alarm or receive emergency notification',
                    'Stop all work immediately',
                    'Assist visitors and those needing help',
                    'Proceed to nearest exit following posted routes',
                    'Do not use elevators',
                    'Report to designated assembly area',
                    'Floor wardens confirm area clear',
                    'Account for all personnel',
                    'Wait for all-clear before re-entering'
                ]
            ],

            // Sales & Marketing SOPs
            [
                'title' => 'Lead Qualification Process',
                'description' => 'Framework for qualifying inbound and outbound leads including scoring criteria and handoff to sales.',
                'summary' => 'Lead scoring and qualification for sales handoff.',
                'category' => 'Sales & Marketing',
                'department' => 'Sales',
                'difficulty' => 2,
                'status' => 'published',
                'tags' => ['customer-facing', 'daily', 'beginner'],
                'steps' => [
                    'Receive new lead notification from CRM',
                    'Research company and contact using LinkedIn and website',
                    'Score lead using BANT criteria (Budget, Authority, Need, Timeline)',
                    'Assign lead score in CRM (1-100)',
                    'Add relevant notes and research findings',
                    'Route qualified leads (score 60+) to sales rep',
                    'Send nurture sequence to unqualified leads',
                    'Track conversion rates for scoring refinement'
                ]
            ],
            [
                'title' => 'Marketing Campaign Launch',
                'description' => 'Checklist for launching marketing campaigns across digital and traditional channels.',
                'summary' => 'Campaign launch checklist across all channels.',
                'category' => 'Sales & Marketing',
                'department' => 'Marketing',
                'difficulty' => 3,
                'status' => 'published',
                'tags' => ['review-required', 'compliance', 'advanced'],
                'steps' => [
                    'Finalize campaign creative assets',
                    'Set up tracking parameters and UTM codes',
                    'Configure landing pages and forms',
                    'Test all links and conversion paths',
                    'Set up analytics dashboards',
                    'Brief sales team on campaign details',
                    'Schedule content across all channels',
                    'Launch campaign per schedule',
                    'Monitor initial performance metrics',
                    'Optimize based on early results'
                ]
            ],
            [
                'title' => 'Sales Proposal Creation',
                'description' => 'Standard process for creating client proposals including pricing, terms, and approval workflow.',
                'summary' => 'Proposal development with pricing and approvals.',
                'category' => 'Sales & Marketing',
                'department' => 'Sales',
                'difficulty' => 3,
                'status' => 'published',
                'tags' => ['customer-facing', 'review-required', 'advanced'],
                'steps' => [
                    'Gather client requirements and scope',
                    'Select appropriate proposal template',
                    'Configure pricing based on deal value',
                    'Add relevant case studies and references',
                    'Include standard terms and conditions',
                    'Route for pricing approval if above threshold',
                    'Generate final PDF for delivery',
                    'Send proposal via CRM for tracking',
                    'Schedule follow-up activities'
                ]
            ],

            // Customer Support SOPs
            [
                'title' => 'Support Ticket Handling',
                'description' => 'Standard workflow for receiving, triaging, and resolving customer support tickets.',
                'summary' => 'End-to-end ticket lifecycle management.',
                'category' => 'Customer Support',
                'department' => 'Support',
                'difficulty' => 1,
                'status' => 'published',
                'tags' => ['customer-facing', 'daily', 'beginner'],
                'steps' => [
                    'Receive and acknowledge ticket within 1 hour',
                    'Categorize by issue type and priority',
                    'Review customer account history',
                    'Attempt first-contact resolution',
                    'Escalate to Tier 2 if unresolved after 30 mins',
                    'Document all troubleshooting steps',
                    'Verify resolution with customer',
                    'Close ticket with resolution summary',
                    'Trigger satisfaction survey'
                ]
            ],
            [
                'title' => 'Customer Escalation Procedure',
                'description' => 'Protocol for handling escalated customer issues including executive involvement and service recovery.',
                'summary' => 'Escalation handling with service recovery options.',
                'category' => 'Customer Support',
                'department' => 'Support',
                'difficulty' => 4,
                'status' => 'published',
                'tags' => ['urgent', 'manager-only', 'customer-facing'],
                'steps' => [
                    'Receive escalation from frontline support',
                    'Review full ticket history and context',
                    'Contact customer within 2 hours',
                    'Listen actively and acknowledge concerns',
                    'Propose resolution and timeline',
                    'Engage additional teams if needed',
                    'Document all actions and outcomes',
                    'Follow up to confirm satisfaction',
                    'Conduct internal post-mortem',
                    'Implement preventive measures if systemic'
                ]
            ],

            // Quality Assurance SOPs
            [
                'title' => 'Software QA Testing Protocol',
                'description' => 'Standard testing procedures for software releases including test planning, execution, and defect management.',
                'summary' => 'Software testing lifecycle and defect tracking.',
                'category' => 'Quality Assurance',
                'department' => 'QA',
                'difficulty' => 3,
                'status' => 'published',
                'tags' => ['mandatory', 'review-required', 'advanced'],
                'steps' => [
                    'Review requirements and user stories',
                    'Create test plan with coverage matrix',
                    'Develop test cases in test management tool',
                    'Set up test environment and data',
                    'Execute test cases systematically',
                    'Log defects with reproduction steps',
                    'Verify defect fixes with regression testing',
                    'Generate test summary report',
                    'Obtain sign-off for release'
                ]
            ],
            [
                'title' => 'Product Quality Inspection',
                'description' => 'Physical product inspection procedures including sampling, defect identification, and acceptance criteria.',
                'summary' => 'Product inspection with sampling and acceptance criteria.',
                'category' => 'Quality Assurance',
                'department' => 'QA',
                'difficulty' => 3,
                'status' => 'draft',
                'tags' => ['mandatory', 'compliance', 'manual'],
                'steps' => [
                    'Receive shipment and verify against PO',
                    'Determine sample size per AQL standards',
                    'Select random samples from lot',
                    'Inspect per product specification checklist',
                    'Document all defects with photos',
                    'Calculate defect rate vs. acceptance threshold',
                    'Approve lot or initiate rejection process',
                    'Complete inspection report',
                    'Notify procurement of results'
                ]
            ],

            // Security & Compliance SOPs
            [
                'title' => 'Data Privacy Compliance',
                'description' => 'Procedures for handling personal data in compliance with GDPR, CCPA, and other privacy regulations.',
                'summary' => 'Privacy regulation compliance for personal data handling.',
                'category' => 'Security & Compliance',
                'department' => 'Legal',
                'difficulty' => 5,
                'status' => 'published',
                'tags' => ['compliance', 'mandatory', 'advanced'],
                'steps' => [
                    'Identify personal data in scope',
                    'Document lawful basis for processing',
                    'Verify consent mechanisms are in place',
                    'Ensure data minimization principles applied',
                    'Configure appropriate retention periods',
                    'Document data flows and third-party sharing',
                    'Implement subject access request process',
                    'Conduct regular privacy impact assessments',
                    'Maintain records of processing activities',
                    'Report breaches within required timeframes'
                ]
            ],
            [
                'title' => 'Security Incident Response',
                'description' => 'Incident response procedures for security breaches including containment, investigation, and communication.',
                'summary' => 'Security incident handling from detection to resolution.',
                'category' => 'Security & Compliance',
                'department' => 'Security',
                'difficulty' => 5,
                'status' => 'published',
                'tags' => ['urgent', 'mandatory', 'compliance'],
                'steps' => [
                    'Detect and report potential incident',
                    'Activate incident response team',
                    'Assess scope and severity',
                    'Implement containment measures',
                    'Preserve evidence for investigation',
                    'Conduct technical investigation',
                    'Determine notification requirements',
                    'Communicate with affected parties',
                    'Implement remediation measures',
                    'Document lessons learned',
                    'Update security controls as needed'
                ]
            ],
        ];

        foreach ($sopData as $index => $data) {
            $sop = new SOP();
            $sop->setTitle($data['title']);
            $sop->setDescription($data['description']);
            $sop->setSummary($data['summary']);
            $sop->setCategory($categories[$data['category']]);
            $sop->setDepartment($data['department']);
            $sop->setDifficulty($data['difficulty']);
            $sop->setStatus($data['status']);
            $sop->setVersionNumber(1);
            $sop->setCreatedBy($users[rand(0, 3)]); // Random editor or admin
            $sop->setCreatedAt(new \DateTimeImmutable('-' . rand(30, 365) . ' days'));
            $sop->setUpdatedAt(new \DateTimeImmutable('-' . rand(1, 29) . ' days'));

            // Add tags
            foreach ($data['tags'] as $tagName) {
                if (isset($tags[$tagName])) {
                    $sop->addTag($tags[$tagName]);
                }
            }

            $manager->persist($sop);
            $sops[] = $sop;

            // Create steps
            foreach ($data['steps'] as $stepNum => $stepContent) {
                $step = new SOPStep();
                $step->setSop($sop);
                $step->setStepNumber($stepNum + 1);
                $step->setContent($stepContent);
                $manager->persist($step);
            }

            // Create a version snapshot for some SOPs
            if (rand(0, 1) === 1) {
                $version = new SOPVersion();
                $version->setSop($sop);
                $version->setVersionNumber(1);
                $version->setContent([
                    'title' => $data['title'],
                    'description' => $data['description'],
                    'steps' => $data['steps']
                ]);
                $version->setCreatedAt($sop->getCreatedAt());
                $version->setCreatedBy($sop->getCreatedBy());
                $manager->persist($version);
            }
        }

        // ===========================================
        // 5. CREATE LEARNING PATHS (5 paths)
        // ===========================================
        $learningPathData = [
            [
                'title' => 'New Employee Onboarding',
                'description' => 'Essential SOPs for new employees to complete within their first 30 days. Covers company policies, systems access, and basic procedures.',
                'sopIndexes' => [0, 5, 15, 19] // Workstation Setup, Employee Onboarding, Emergency Evacuation, Support Tickets
            ],
            [
                'title' => 'IT Team Essentials',
                'description' => 'Core procedures for IT team members covering support, security, and infrastructure management.',
                'sopIndexes' => [0, 1, 2, 3, 4, 24] // IT SOPs + Security Incident
            ],
            [
                'title' => 'Manager Training Path',
                'description' => 'Required reading for all people managers including performance reviews, team management, and compliance.',
                'sopIndexes' => [6, 7, 8, 20] // Performance Review, Termination, Time Off, Escalation
            ],
            [
                'title' => 'Finance Department Basics',
                'description' => 'Core financial procedures for new finance team members.',
                'sopIndexes' => [9, 10, 11] // Invoice, Expense, Monthly Close
            ],
            [
                'title' => 'Compliance & Security',
                'description' => 'Mandatory security and compliance training for all employees with system access.',
                'sopIndexes' => [13, 23, 24] // Facility Access, Data Privacy, Security Incident
            ],
        ];

        foreach ($learningPathData as $pathData) {
            $path = new LearningPath();
            $path->setTitle($pathData['title']);
            $path->setDescription($pathData['description']);
            $path->setCreatedBy($users[0]); // Admin
            $path->setCreatedAt(new \DateTimeImmutable('-' . rand(30, 180) . ' days'));
            $manager->persist($path);

            // Add SOPs to the path
            foreach ($pathData['sopIndexes'] as $order => $sopIndex) {
                if (isset($sops[$sopIndex])) {
                    $item = new LearningPathItem();
                    $item->setLearningPath($path);
                    $item->setSop($sops[$sopIndex]);
                    $item->setPosition($order + 1);
                    $manager->persist($item);
                }
            }
        }

        // ===========================================
        // 6. CREATE USER PROGRESS (varied progress)
        // ===========================================
        $statuses = ['not_started', 'in_progress', 'completed'];

        // Create progress for regular users (indexes 4-9)
        for ($i = 4; $i < 10; $i++) {
            $user = $users[$i];
            // Each user has progress on 5-10 random SOPs
            $numSops = rand(5, 10);
            $selectedSops = array_rand($sops, min($numSops, count($sops)));
            
            foreach ((array)$selectedSops as $sopIndex) {
                $progress = new UserProgress();
                $progress->setOwner($user);
                $progress->setSop($sops[$sopIndex]);
                
                $status = $statuses[rand(0, 2)];
                $progress->setStatus($status);
                
                if ($status === 'completed') {
                    $progress->setCompletedAt(new \DateTimeImmutable('-' . rand(1, 60) . ' days'));
                }
                
                $manager->persist($progress);
            }
        }

        $manager->flush();
    }
}
