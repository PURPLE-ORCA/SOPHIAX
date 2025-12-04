<?php

namespace App\Entity;

use App\Repository\LearningPathItemRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LearningPathItemRepository::class)]
class LearningPathItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $position = null;

    #[ORM\ManyToOne(inversedBy: 'items')]
    #[ORM\JoinColumn(nullable: false)]
    private ?LearningPath $learningPath = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?SOP $sop = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(int $position): static
    {
        $this->position = $position;

        return $this;
    }

    public function getLearningPath(): ?LearningPath
    {
        return $this->learningPath;
    }

    public function setLearningPath(?LearningPath $learningPath): static
    {
        $this->learningPath = $learningPath;

        return $this;
    }

    public function getSop(): ?SOP
    {
        return $this->sop;
    }

    public function setSop(?SOP $sop): static
    {
        $this->sop = $sop;

        return $this;
    }
}
