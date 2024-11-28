<?php

namespace App\Entity;

use App\Repository\MissionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MissionRepository::class)]
class Mission
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_UPCOMING = 'upcoming'; // Statut pour une mission future

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    #[ORM\Column(length: 255, options: ['default' => self::STATUS_PENDING])]
    private ?string $status = self::STATUS_PENDING; // Défaut : 'pending'

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $startAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $endAt = null;

    #[ORM\Column(length: 255)]
    private ?string $location = null;

    #[ORM\Column]
    private ?int $dangerLevel = null;

    #[ORM\ManyToOne(inversedBy: 'missions')]
    private ?Team $assignedTeam = null;

    #[ORM\ManyToMany(targetEntity: Power::class)]
    private Collection $requiredPowers;

    #[ORM\Column(type: 'boolean', nullable: true)]
    private ?bool $isSuccessful = null;

    public function __construct()
    {
        $this->requiredPowers = new ArrayCollection();
    }

    // -------------------------------
    // Getters and Setters
    // -------------------------------

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

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        if (!in_array($status, self::getValidStatuses(), true)) {
            throw new \InvalidArgumentException("Statut invalide : $status");
        }

        $this->status = $status;

        return $this;
    }

    public static function getValidStatuses(): array
    {
        return [
            self::STATUS_PENDING,
            self::STATUS_IN_PROGRESS,
            self::STATUS_COMPLETED,
            self::STATUS_UPCOMING,
        ];
    }

    public function getStartAt(): ?\DateTimeInterface
    {
        return $this->startAt;
    }

    public function setStartAt(\DateTimeInterface $startAt): static
    {
        $this->startAt = $startAt;

        return $this;
    }

    public function getEndAt(): ?\DateTimeInterface
    {
        return $this->endAt;
    }

    public function setEndAt(\DateTimeInterface $endAt): static
    {
        $this->endAt = $endAt;

        return $this;
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation(string $location): static
    {
        $this->location = $location;

        return $this;
    }

    public function getDangerLevel(): ?int
    {
        return $this->dangerLevel;
    }

    public function setDangerLevel(int $dangerLevel): static
    {
        $this->dangerLevel = $dangerLevel;

        return $this;
    }

    public function getAssignedTeam(): ?Team
    {
        return $this->assignedTeam;
    }

    public function setAssignedTeam(?Team $assignedTeam): static
    {
        $this->assignedTeam = $assignedTeam;

        return $this;
    }

    /**
     * @return Collection<int, Power>
     */
    public function getRequiredPowers(): Collection
    {
        return $this->requiredPowers;
    }

    public function addRequiredPower(Power $power): static
    {
        if (!$this->requiredPowers->contains($power)) {
            $this->requiredPowers->add($power);
        }

        return $this;
    }

    public function removeRequiredPower(Power $power): static
    {
        $this->requiredPowers->removeElement($power);

        return $this;
    }

    public function getIsSuccessful(): ?bool
    {
        return $this->isSuccessful;
    }

    public function setIsSuccessful(?bool $isSuccessful): static
    {
        $this->isSuccessful = $isSuccessful;

        return $this;
    }

    /**
     * Méthode utilitaire pour vérifier si une mission est réussie.
     */
    public function isSuccessful(): bool
    {
        return $this->isSuccessful ?? false;
    }

    // -------------------------------
    // Custom Methods
    // -------------------------------

    /**
     * Mise à jour automatique du statut de la mission.
     */
    public function updateStatus(): void
    {
        $now = new \DateTime(); // Date et heure actuelles
        $now->setTime(0, 0, 0); // Normalise "now" à minuit
    
        if ($this->startAt instanceof \DateTimeInterface && $this->endAt instanceof \DateTimeInterface) {
            // Normalise les dates à minuit
            $startAt = ($this->startAt instanceof \DateTime) ? clone $this->startAt : \DateTime::createFromInterface($this->startAt);
            $endAt = ($this->endAt instanceof \DateTime) ? clone $this->endAt : \DateTime::createFromInterface($this->endAt);
    
            $startAt->setTime(0, 0, 0);
            $endAt->setTime(0, 0, 0);
    
            error_log("Mise à jour du statut : startAt={$startAt->format('Y-m-d')}, endAt={$endAt->format('Y-m-d')}, now={$now->format('Y-m-d')}");
    
            // Logique de mise à jour du statut
            if ($startAt > $now) {
                $this->setStatus(self::STATUS_UPCOMING);
                error_log("Statut mis à jour : À venir");
            } elseif ($startAt <= $now && $endAt >= $now) {
                $this->setStatus(self::STATUS_IN_PROGRESS);
                error_log("Statut mis à jour : En cours");
            } elseif ($endAt < $now) {
                $this->setStatus(self::STATUS_COMPLETED);
                error_log("Statut mis à jour : Terminée");
            }
        } else {
            error_log("Les dates startAt ou endAt ne sont pas définies ou sont invalides.");
        }
    }
    
    
    
    
}
