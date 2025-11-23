<?php

namespace App\Entity;

use App\Repository\MouvementCaisseRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: MouvementCaisseRepository::class)]
class MouvementCaisse
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['mouvement_caisse'])]
    private ?int $id = null;

    #[ORM\Column(length: 20)]
    #[Groups(['mouvement_caisse'])]
    private ?string $type = null; // 'avance', 'depense', 'remboursement'

    #[ORM\Column]
    #[Groups(['mouvement_caisse'])]
    private ?float $montant = null;

    #[ORM\Column(length: 255)]
    #[Groups(['mouvement_caisse'])]
    private ?string $gestionnaire = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['mouvement_caisse'])]
    private ?string $description = null;

    #[ORM\Column]
    #[Groups(['mouvement_caisse'])]
    private ?bool $rembourse = false;

    #[ORM\Column(nullable: true)]
    #[Groups(['mouvement_caisse'])]
    private ?float $montantRemboursePartiel = 0.0;

    #[ORM\Column]
    #[Groups(['mouvement_caisse'])]
    private ?\DateTime $date = null;

    public function __construct()
    {
        $this->date = new \DateTime();
        $this->rembourse = false;
        $this->montantRemboursePartiel = 0.0;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;
        return $this;
    }

    public function getMontant(): ?float
    {
        return $this->montant;
    }

    public function setMontant(float $montant): static
    {
        $this->montant = $montant;
        return $this;
    }

    public function getGestionnaire(): ?string
    {
        return $this->gestionnaire;
    }

    public function setGestionnaire(string $gestionnaire): static
    {
        $this->gestionnaire = $gestionnaire;
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

    public function isRembourse(): ?bool
    {
        return $this->rembourse;
    }

    public function setRembourse(bool $rembourse): static
    {
        $this->rembourse = $rembourse;
        return $this;
    }

    public function getMontantRemboursePartiel(): ?float
    {
        return $this->montantRemboursePartiel;
    }

    public function setMontantRemboursePartiel(?float $montantRemboursePartiel): static
    {
        $this->montantRemboursePartiel = $montantRemboursePartiel;
        return $this;
    }

    public function getMontantRestant(): float
    {
        if ($this->rembourse) {
            return 0.0;
        }

        $montantTotal = $this->montant;
        $montantDejaRembourse = $this->montantRemboursePartiel ?? 0.0;
        
        return max(0, $montantTotal - $montantDejaRembourse);
    }

    public function isPartiellementRembourse(): bool
    {
        return !$this->rembourse && ($this->montantRemboursePartiel > 0);
    }

    public function getDate(): ?\DateTime
    {
        return $this->date;
    }

    public function setDate(\DateTime $date): static
    {
        $this->date = $date;
        return $this;
    }
}