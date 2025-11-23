<?php

namespace App\DataFixtures;

use App\Entity\Adherent;
use App\Entity\Vente;
use App\Entity\Tournee;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // -------------------
        // Adhérent 1
        // -------------------
        $adherent1 = new Adherent();
        $adherent1->setNom('Dupont')
            ->setCreditTotal(50.0);
        $manager->persist($adherent1);

        // Ventes de l'adhérent 1
        $vente1a = new Vente();
        $vente1a->setAdherent($adherent1)
            ->setMontantTotal(30.0)
            ->setMontantPaye(10.0)
            ->setResteAPayer(20.0)
            ->setModePaiement('tout_au_credit')
            ->setDate(new \DateTime('-2 days'));
        $manager->persist($vente1a);

        $vente1b = new Vente();
        $vente1b->setAdherent($adherent1)
            ->setMontantTotal(20.0)
            ->setMontantPaye(20.0)
            ->setResteAPayer(0.0)
            ->setModePaiement('paiement_complet')
            ->setDate(new \DateTime('-1 day'));
        $manager->persist($vente1b);

        // -------------------
        // Adhérent 2
        // -------------------
        $adherent2 = new Adherent();
        $adherent2->setNom('Martin')
            ->setCreditTotal(20.0);
        $manager->persist($adherent2);

        // Vente adhérent 2
        $vente2 = new Vente();
        $vente2->setAdherent($adherent2)
            ->setMontantTotal(15.0)
            ->setMontantPaye(15.0)
            ->setResteAPayer(0.0)
            ->setModePaiement('paiement_complet')
            ->setDate(new \DateTime('-1 day'));
        $manager->persist($vente2);

        // -------------------
        // Adhérent 3
        // -------------------
        $adherent3 = new Adherent();
        $adherent3->setNom('Durand')
            ->setCreditTotal(0.0);
        $manager->persist($adherent3);

        // Vente adhérent 3
        $vente3 = new Vente();
        $vente3->setAdherent($adherent3)
            ->setMontantTotal(10.0)
            ->setMontantPaye(0.0)
            ->setResteAPayer(10.0)
            ->setModePaiement('tout_au_credit')
            ->setDate(new \DateTime('today'));
        $manager->persist($vente3);

        $manager->flush();
    }
}
