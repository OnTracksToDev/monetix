<?php

namespace App\Entity;

use App\Repository\AdherentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: AdherentRepository::class)]
class Adherent
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['adherent', 'adherent_details', 'tournee', 'vente'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['adherent', 'adherent_details', 'tournee', 'vente'])]
    private ?string $nom = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['adherent', 'adherent_details'])]
    private ?float $credit_total = null;

    #[ORM\OneToMany(mappedBy: 'adherent', targetEntity: Vente::class)]
    #[Groups(['adherent_details'])]
    private Collection $ventes;

    #[ORM\OneToMany(mappedBy: 'adherent', targetEntity: Tournee::class)]
    #[Groups(['adherent_details'])]
    private Collection $tournees;

    public function __construct()
    {
        $this->ventes = new ArrayCollection();
        $this->tournees = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;
        return $this;
    }

    public function getCreditTotal(): ?float
    {
        return $this->credit_total;
    }

    public function setCreditTotal(?float $credit_total): static
    {
        $this->credit_total = $credit_total;
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
            $vente->setAdherent($this);
        }
        return $this;
    }

    public function removeVente(Vente $vente): static
    {
        if ($this->ventes->removeElement($vente)) {
            if ($vente->getAdherent() === $this) {
                $vente->setAdherent(null);
            }
        }
        return $this;
    }

    /** @return Collection<int, Tournee> */
    public function getTournees(): Collection
    {
        return $this->tournees;
    }

    public function addTournee(Tournee $tournee): static
    {
        if (!$this->tournees->contains($tournee)) {
            $this->tournees->add($tournee);
            $tournee->setAdherent($this);
        }
        return $this;
    }

    public function removeTournee(Tournee $tournee): static
    {
        if ($this->tournees->removeElement($tournee)) {
            if ($tournee->getAdherent() === $this) {
                $tournee->setAdherent(null);
            }
        }
        return $this;
    }
}
