<?php

namespace App\EventListener;

use App\Entity\Tournee;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;

#[AsEntityListener(event: Events::preUpdate, entity: Tournee::class)]
#[AsEntityListener(event: Events::prePersist, entity: Tournee::class)]
class TourneeStatutListener
{
    public function preUpdate(Tournee $tournee, PreUpdateEventArgs $event): void
    {
        $this->handleStatutChange($tournee, $event);
    }

    public function prePersist(Tournee $tournee): void
    {
        $this->handleStatutChange($tournee);
    }

    private function handleStatutChange(Tournee $tournee, PreUpdateEventArgs $event = null): void
    {
        if ($event && $event->hasChangedField('statut')) {
            // Pour preUpdate : on a détecté un changement de statut
            $ancienStatut = $event->getOldValue('statut');
            $nouveauStatut = $event->getNewValue('statut');
            
            $this->applyStatutRules($tournee, $ancienStatut, $nouveauStatut);
        } elseif (!$event) {
            // Pour prePersist : création
            if ($tournee->getStatut() === 'cloturee' && $tournee->getDateFin() === null) {
                $tournee->setDateFin(new \DateTime());
            }
        }
    }

    private function applyStatutRules(Tournee $tournee, string $ancienStatut, string $nouveauStatut): void
    {
        // RÈGLE 1 : Passage à "cloturee" → date_fin = maintenant
        if ($nouveauStatut === 'cloturee' && $ancienStatut !== 'cloturee') {
            $tournee->setDateFin(new \DateTime());
        }
        
        // RÈGLE 2 : Passage à "en_cours" → date_fin = null
        if ($nouveauStatut === 'en_cours' && $ancienStatut === 'cloturee') {
            $tournee->setDateFin(null);
        }
    }
}