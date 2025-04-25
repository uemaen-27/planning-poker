<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, unique: true)]
    private ?string $username = null;

    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column]
    private array $roles = [];

    #[ORM\Column]
    private ?bool $isHost = null;

    #[ORM\OneToMany(targetEntity: Session::class, mappedBy: 'host')]
    private Collection $sessions;

    #[ORM\OneToMany(targetEntity: Estimate::class, mappedBy: 'participant')]
    private Collection $estimates;

    public function __construct()
    {
        $this->sessions = new ArrayCollection();
        $this->estimates = new ArrayCollection();
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->id;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;
        return $this;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;
        return $this;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        // Garantiert, dass jeder Benutzer mindestens ROLE_USER hat
        $roles[] = 'ROLE_USER';
        return array_unique($roles);
    }

    public function setRoles(?array $roles): self
    {
        $this->roles = $roles;
        return $this;
    }

    public function getSalt(): ?string
    {
        // Da bcrypt oder sodium genutzt wird, wird kein Salt benötigt
        return null;
    }

    public function eraseCredentials(): void
    {
        // Falls sensible Daten gespeichert werden, können sie hier entfernt werden
    }

    public function isHost(): ?bool
    {
        return $this->isHost;
    }

    public function setHost(bool $isHost): self
    {
        $this->isHost = $isHost;
        return $this;
    }

    public function getSessions(): Collection
    {
        return $this->sessions;
    }

    public function addSession(Session $session): self
    {
        if (!$this->sessions->contains($session)) {
            $this->sessions->add($session);
            $session->setHost($this);
        }

        return $this;
    }

    public function removeSession(Session $session): self
    {
        if ($this->sessions->removeElement($session)) {
            if ($session->getHost() === $this) {
                $session->setHost(null);
            }
        }

        return $this;
    }

    public function getEstimates(): Collection
    {
        return $this->estimates;
    }

    public function addEstimate(Estimate $estimate): self
    {
        if (!$this->estimates->contains($estimate)) {
            $this->estimates->add($estimate);
            $estimate->setParticipant($this);
        }

        return $this;
    }

    public function removeEstimate(Estimate $estimate): self
    {
        if ($this->estimates->removeElement($estimate)) {
            if ($estimate->getParticipant() === $this) {
                $estimate->setParticipant(null);
            }
        }

        return $this;
    }
}
