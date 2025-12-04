<?php

namespace App\Entity;

use App\Repository\TagRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TagRepository::class)]
class Tag
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $name = null;

    /**
     * @var Collection<int, SOP>
     */
    #[ORM\ManyToMany(targetEntity: SOP::class, mappedBy: 'tags')]
    private Collection $sops;

    public function __construct()
    {
        $this->sops = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection<int, SOP>
     */
    public function getSops(): Collection
    {
        return $this->sops;
    }

    public function addSop(SOP $sop): static
    {
        if (!$this->sops->contains($sop)) {
            $this->sops->add($sop);
            $sop->addTag($this);
        }

        return $this;
    }

    public function removeSop(SOP $sop): static
    {
        if ($this->sops->removeElement($sop)) {
            $sop->removeTag($this);
        }

        return $this;
    }
}
