<?php

namespace App\Entity;

use App\Repository\EstimateRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EstimateRepository::class)]
class Estimate
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(nullable: true)]
    private ?int $value = null;

    #[ORM\Column]
    private ?bool $revealed = null;

    #[ORM\ManyToOne(inversedBy: 'estimates')]
    private ?User $participant = null;

    #[ORM\ManyToOne(inversedBy: 'estimates')]
    private ?Session $session = null;

    #[ORM\ManyToOne(inversedBy: 'estimates')]
    private ?ProductBacklogItem $productBacklogItem = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getValue(): ?int
    {
        return $this->value;
    }

    public function setValue(?int $value): static
    {
        $this->value = $value;

        return $this;
    }

    public function isRevealed(): ?bool
    {
        return $this->revealed;
    }

    public function setRevealed(bool $revealed): static
    {
        $this->revealed = $revealed;

        return $this;
    }

    public function getParticipant(): ?User
    {
        return $this->participant;
    }

    public function setParticipant(?User $participant): static
    {
        $this->participant = $participant;

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

    public function getProductBacklogItem(): ?ProductBacklogItem
    {
        return $this->productBacklogItem;
    }

    public function setProductBacklogItem(?ProductBacklogItem $productBacklogItem): static
    {
        $this->productBacklogItem = $productBacklogItem;

        return $this;
    }
}
