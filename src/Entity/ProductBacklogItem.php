<?php

namespace App\Entity;

use App\Repository\ProductBacklogItemRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProductBacklogItemRepository::class)]
class ProductBacklogItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $description = null;

    #[ORM\ManyToOne(inversedBy: 'productBacklogItems')]
    private ?Session $session = null;

    /**
     * @var Collection<int, Estimate>
     */
    #[ORM\OneToMany(targetEntity: Estimate::class, mappedBy: 'productBacklogItem')]
    private Collection $estimates;

    public function __construct()
    {
        $this->estimates = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getSession(): ?Session
    {
        return $this->session;
    }

    public function setSession(?Session $session): static
    {
        $this->session = $session;

        return $this;
    }

    /**
     * @return Collection<int, Estimate>
     */
    public function getEstimates(): Collection
    {
        return $this->estimates;
    }

    public function addEstimate(Estimate $estimate): static
    {
        if (!$this->estimates->contains($estimate)) {
            $this->estimates->add($estimate);
            $estimate->setProductBacklogItem($this);
        }

        return $this;
    }

    public function removeEstimate(Estimate $estimate): static
    {
        if ($this->estimates->removeElement($estimate)) {
            // set the owning side to null (unless already changed)
            if ($estimate->getProductBacklogItem() === $this) {
                $estimate->setProductBacklogItem(null);
            }
        }

        return $this;
    }
}
