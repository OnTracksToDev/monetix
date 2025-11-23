<?php

namespace App\Entity;

use App\Repository\VenteRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: VenteRepository::class)]
class Vente
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['vente', 'adherent_details'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'ventes')]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    #[Groups(['vente'])]
    private ?Adherent $adherent = null;

    #[ORM\ManyToOne(inversedBy: 'ventes')]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['vente'])]
    private ?Tournee $tournee = null;

    #[ORM\Column]
    #[Groups(['vente', 'adherent_details', 'tournee_details'])]
    private ?float $montant_total = null;

    #[ORM\Column]
    #[Groups(['vente', 'adherent_details'])]
    private ?float $montant_paye = null;

    #[ORM\Column]
    #[Groups(['vente', 'adherent_details'])]
    private ?float $reste_a_payer = null;

    #[ORM\Column(length: 20)]
    #[Groups(['vente', 'adherent_details'])]
    private ?string $mode_paiement = null;

    #[ORM\Column]
    #[Groups(['vente', 'adherent_details'])]
    private ?\DateTime $date = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAdherent(): ?Adherent
    {
        return $this->adherent;
    }

    public function setAdherent(?Adherent $adherent): static
    {
        $this->adherent = $adherent;
        return $this;
    }

    public function getTournee(): ?Tournee
    {
        return $this->tournee;
    }

    public function setTournee(?Tournee $tournee): static
    {
        $this->tournee = $tournee;
        return $this;
    }

    public function getMontantTotal(): ?float
    {
        return $this->montant_total;
    }

    public function setMontantTotal(float $montant_total): static
    {
        $this->montant_total = $montant_total;
        return $this;
    }

    public function getMontantPaye(): ?float
    {
        return $this->montant_paye;
    }

    public function setMontantPaye(float $montant_paye): static
    {
        $this->montant_paye = $montant_paye;
        return $this;
    }

    public function getResteAPayer(): ?float
    {
        return $this->reste_a_payer;
    }

    public function setResteAPayer(float $reste_a_payer): static
    {
        $this->reste_a_payer = $reste_a_payer;
        return $this;
    }

    public function getModePaiement(): ?string
    {
        return $this->mode_paiement;
    }

    public function setModePaiement(string $mode_paiement): static
    {
        $this->mode_paiement = $mode_paiement;
        return $this;
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
