<?php

namespace App\Controller\Api;

use App\Entity\MouvementCaisse;
use App\Repository\MouvementCaisseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/mouvements-caisse')]
class MouvementCaisseController extends AbstractController
{
    #[Route('', name: 'api_mouvements_caisse_index', methods: ['GET'])]
    public function index(MouvementCaisseRepository $repository, SerializerInterface $serializer): JsonResponse
    {
        $mouvements = $repository->findBy([], ['date' => 'DESC']);
        
        return new JsonResponse(
            $serializer->serialize($mouvements, 'json', ['groups' => 'mouvement_caisse']),
            JsonResponse::HTTP_OK,
            [],
            true
        );
    }

    #[Route('/recent', name: 'api_mouvements_caisse_recent', methods: ['GET'])]
    public function recent(MouvementCaisseRepository $repository, SerializerInterface $serializer): JsonResponse
    {
        $mouvements = $repository->findRecentMouvements(10);
        
        return new JsonResponse(
            $serializer->serialize($mouvements, 'json', ['groups' => 'mouvement_caisse']),
            JsonResponse::HTTP_OK,
            [],
            true
        );
    }

    #[Route('/create', name: 'api_mouvements_caisse_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em, SerializerInterface $serializer): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $mouvement = new MouvementCaisse();
        $mouvement->setType($data['type']);
        $mouvement->setMontant($data['montant']);
        $mouvement->setGestionnaire($data['gestionnaire']);
        $mouvement->setDescription($data['description'] ?? null);
        
        if (isset($data['date'])) {
            $mouvement->setDate(new \DateTime($data['date']));
        }

        $em->persist($mouvement);
        $em->flush();

        return new JsonResponse(
            $serializer->serialize($mouvement, 'json', ['groups' => 'mouvement_caisse']),
            JsonResponse::HTTP_CREATED,
            [],
            true
        );
    }

    #[Route('/{id}/rembourser', name: 'api_mouvements_caisse_rembourser', methods: ['POST'])]
    public function rembourser(MouvementCaisse $mouvement, EntityManagerInterface $em, SerializerInterface $serializer): JsonResponse
    {
        if ($mouvement->getType() !== 'avance') {
            return new JsonResponse(['error' => 'Seules les avances peuvent être remboursées'], 400);
        }

        $mouvement->setRembourse(true);
        $mouvement->setMontantRemboursePartiel($mouvement->getMontant()); // Marque comme complètement remboursé
        $em->flush();

        return new JsonResponse(
            $serializer->serialize($mouvement, 'json', ['groups' => 'mouvement_caisse']),
            JsonResponse::HTTP_OK,
            [],
            true
        );
    }

    #[Route('/{id}/rembourser-partiel', name: 'api_mouvements_caisse_rembourser_partiel', methods: ['POST'])]
    public function rembourserPartiel(MouvementCaisse $mouvement, Request $request, EntityManagerInterface $em, SerializerInterface $serializer): JsonResponse
    {
        if ($mouvement->getType() !== 'avance') {
            return new JsonResponse(['error' => 'Seules les avances peuvent être remboursées partiellement'], 400);
        }

        if ($mouvement->isRembourse()) {
            return new JsonResponse(['error' => 'Cette avance est déjà complètement remboursée'], 400);
        }

        $data = json_decode($request->getContent(), true);
        $nouveauMontantRembourse = $data['montantRemboursePartiel'] ?? null;

        if ($nouveauMontantRembourse === null || !is_numeric($nouveauMontantRembourse)) {
            return new JsonResponse(['error' => 'Le montant remboursé partiel est requis'], 400);
        }

        $nouveauMontantRembourse = (float) $nouveauMontantRembourse;
        $montantTotal = $mouvement->getMontant();

        if ($nouveauMontantRembourse <= 0) {
            return new JsonResponse(['error' => 'Le montant remboursé doit être positif'], 400);
        }

        if ($nouveauMontantRembourse > $montantTotal) {
            return new JsonResponse(['error' => 'Le montant remboursé ne peut pas dépasser le montant total de l\'avance'], 400);
        }

        // Met à jour le montant partiellement remboursé
        $mouvement->setMontantRemboursePartiel($nouveauMontantRembourse);

        // Si le montant remboursé partiel est égal au montant total, marque comme complètement remboursé
        if ($nouveauMontantRembourse >= $montantTotal) {
            $mouvement->setRembourse(true);
            $mouvement->setMontantRemboursePartiel($montantTotal);
        }

        $em->flush();

        return new JsonResponse(
            $serializer->serialize($mouvement, 'json', ['groups' => 'mouvement_caisse']),
            JsonResponse::HTTP_OK,
            [],
            true
        );
    }

    #[Route('/dettes-gestionnaires', name: 'api_mouvements_caisse_dettes_gestionnaires', methods: ['GET'])]
    public function dettesGestionnaires(MouvementCaisseRepository $repository): JsonResponse
    {
        $dettes = $repository->getTotalDettesParGestionnaire();
        
        $result = [];
        foreach ($dettes as $gestionnaire => $montant) {
            $result[] = [
                'nom' => $gestionnaire,
                'montant' => $montant
            ];
        }

        return new JsonResponse($result);
    }

    #[Route('/stats', name: 'api_mouvements_caisse_stats', methods: ['GET'])]
    public function stats(MouvementCaisseRepository $repository): JsonResponse
    {
        $stats = [
            'total_avances' => $repository->getTotalByType('avance'),
            'total_depenses' => $repository->getTotalByType('depense'),
            'total_remboursements' => $repository->getTotalByType('remboursement'),
            'total_remboursements_partiels' => $repository->getTotalRemboursementsPartiels(),
            'avances_non_remboursees' => $repository->getTotalByType('avance') - $repository->getTotalByType('remboursement')
        ];

        return new JsonResponse($stats);
    }
}