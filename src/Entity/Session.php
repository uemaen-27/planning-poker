<?php

namespace App\Entity;

use App\Repository\SessionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SessionRepository::class)]
class Session
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $sessionKey = null;

    #[ORM\Column(length: 255)]
    private ?string $estimationType = null;

    #[ORM\Column(type: Types::ARRAY, nullable: true)]
    private ?array $customHours = null;

    #[ORM\Column(length: 255)]
    private ?string $revealMode = null;

    #[ORM\ManyToOne(inversedBy: 'sessions')]
    private ?User $host = null;

    /**
     * @var Collection<int, User>
     */
    #[ORM\ManyToMany(targetEntity: User::class, inversedBy: 'sessions')]
    private Collection $participants;

    /**
     * @var Collection<int, Estimate>
     */
    #[ORM\OneToMany(targetEntity: Estimate::class, mappedBy: 'session')]
    private Collection $estimates;

    /**
     * @var Collection<int, ProductBacklogItem>
     */
    #[ORM\OneToMany(targetEntity: ProductBacklogItem::class, mappedBy: 'session')]
    private Collection $productBacklogItems;

    /**
     * @var Collection<int, SessionCard>
     */
    #[ORM\OneToMany(targetEntity: SessionCard::class, mappedBy: 'session')]
    private Collection $sessionCards;

    #[ORM\ManyToOne(targetEntity: ProductBacklogItem::class, inversedBy: 'sessions')]
    #[ORM\JoinColumn(nullable: true)]
    private ?ProductBacklogItem $activePbi = null;

    public function __construct()
    {
        $this->participants = new ArrayCollection();
        $this->estimates = new ArrayCollection();
        $this->productBacklogItems = new ArrayCollection();
        $this->sessionCards = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSessionKey(): ?string
    {
        return $this->sessionKey;
    }

    public function setSessionKey(string $sessionKey): static
    {
        $this->sessionKey = $sessionKey;

        return $this;
    }

    public function getEstimationType(): ?string
    {
        return $this->estimationType;
    }

    public function setEstimationType(string $estimationType): static
    {
        $this->estimationType = $estimationType;

        return $this;
    }

    public function getCustomHours(): ?array
    {
        return $this->customHours;
    }

    public function setCustomHours(?array $customHours): static
    {
        $this->customHours = $customHours;

        return $this;
    }

    public function getRevealMode(): ?string
    {
        return $this->revealMode;
    }

    public function setRevealMode(string $revealMode): static
    {
        $this->revealMode = $revealMode;

        return $this;
    }

    public function getHost(): ?User
    {
        return $this->host;
    }

    public function setHost(?User $host): static
    {
        $this->host = $host;

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getParticipants(): Collection
    {
        return $this->participants;
    }

    public function addParticipant(User $participant): static
    {
        if (!$this->participants->contains($participant)) {
            $this->participants->add($participant);
        }

        return $this;
    }

    public function removeParticipant(User $participant): static
    {
        $this->participants->removeElement($participant);

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
            $estimate->setSession($this);
        }

        return $this;
    }

    public function removeEstimate(Estimate $estimate): static
    {
        if ($this->estimates->removeElement($estimate)) {
            // set the owning side to null (unless already changed)
            if ($estimate->getSession() === $this) {
                $estimate->setSession(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ProductBacklogItem>
     */
    public function getProductBacklogItems(): Collection
    {
        return $this->productBacklogItems;
    }

    public function addProductBacklogItem(ProductBacklogItem $productBacklogItem): static
    {
        if (!$this->productBacklogItems->contains($productBacklogItem)) {
            $this->productBacklogItems->add($productBacklogItem);
            $productBacklogItem->setSession($this);
        }

        return $this;
    }

    public function removeProductBacklogItem(ProductBacklogItem $productBacklogItem): static
    {
        if ($this->productBacklogItems->removeElement($productBacklogItem)) {
            // set the owning side to null (unless already changed)
            if ($productBacklogItem->getSession() === $this) {
                $productBacklogItem->setSession(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, SessionCard>
     */
    public function getSessionCards(): Collection
    {
        return $this->sessionCards;
    }

    public function addSessionCard(SessionCard $sessionCard): static
    {
        if (!$this->sessionCards->contains($sessionCard)) {
            $this->sessionCards->add($sessionCard);
            $sessionCard->setSession($this);
        }

        return $this;
    }

    public function removeSessionCard(SessionCard $sessionCard): static
    {
        if ($this->sessionCards->removeElement($sessionCard)) {
            // set the owning side to null (unless already changed)
            if ($sessionCard->getSession() === $this) {
                $sessionCard->setSession(null);
            }
        }

        return $this;
    }

    public function getActivePbi(): ?ProductBacklogItem
    {
        return $this->activePbi;
    }

    public function setActivePbi(?ProductBacklogItem $activePbi): static
    {
        $this->activePbi = $activePbi;
        return $this;
    }
}
