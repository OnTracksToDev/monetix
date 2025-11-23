<?php

namespace App\Entity;

use App\Repository\TourneeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: TourneeRepository::class)]
class Tournee
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['tournee', 'adherent_details'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'tournees')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['tournee'])]
    private ?Adherent $adherent = null;

    #[ORM\OneToMany(mappedBy: 'tournee', targetEntity: Vente::class)]
    #[Groups(['tournee', 'tournee_details'])]
    private Collection $ventes;

    #[ORM\Column]
    #[Groups(['tournee', 'adherent_details'])]
    private ?float $montant_total = null;

    #[ORM\Column]
    #[Groups(['tournee', 'adherent_details'])]
    private ?float $montant_paye = null;

    #[ORM\Column]
    #[Groups(['tournee', 'adherent_details'])]
    private ?float $reste_a_payer = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['tournee', 'adherent_details'])]
    private ?int $estimation_clients = null;

    #[ORM\Column]
    #[Groups(['tournee', 'adherent_details'])]
    private ?\DateTime $date_debut = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['tournee', 'adherent_details'])]
    private ?\DateTime $date_fin = null;

    #[ORM\Column(length: 20)]
    #[Groups(['tournee', 'adherent_details'])]
    private ?string $statut = 'en_cours';


    public function __construct()
    {
        $this->ventes = new ArrayCollection();
    }

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

    /** @return Collection<int, Vente> */
    public function getVentes(): Collection
    {
        return $this->ventes;
    }

    public function addVente(Vente $vente): static
    {
        if (!$this->ventes->contains($vente)) {
            $this->ventes->add($vente);
            $vente->setTournee($this);
        }
        return $this;
    }

    public function removeVente(Vente $vente): static
    {
        if ($this->ventes->removeElement($vente)) {
            if ($vente->getTournee() === $this) {
                $vente->setTournee(null);
            }
        }
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

    public function getEstimationClients(): ?int
    {
        return $this->estimation_clients;
    }

    public function setEstimationClients(?int $estimation_clients): static
    {
        $this->estimation_clients = $estimation_clients;
        return $this;
    }

    public function getDateDebut(): ?\DateTime
    {
        return $this->date_debut;
    }

    public function setDateDebut(\DateTime $date_debut): static
    {
        $this->date_debut = $date_debut;
        return $this;
    }

    public function getDateFin(): ?\DateTime
    {
        return $this->date_fin;
    }

    public function setDateFin(?\DateTime $date_fin): static
    {
        $this->date_fin = $date_fin;
        return $this;
    }

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): static
    {
        $this->statut = $statut;
        return $this;
    }
}
