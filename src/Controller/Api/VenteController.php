<?php

namespace App\Controller\Api;

use App\Entity\Vente;
use App\Entity\Adherent;
use App\Entity\Tournee;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/api/ventes')]
class VenteController extends AbstractController
{
    #[Route('', name: 'api_vente_list', methods: ['GET'])]
    public function list(EntityManagerInterface $em, SerializerInterface $serializer): JsonResponse
    {
        $ventes = $em->getRepository(Vente::class)->findAll();
        $data = $serializer->serialize($ventes, 'json', ['groups' => ['vente']]);
        return new JsonResponse($data, 200, [], true);
    }

    #[Route('/{id}', name: 'api_vente_show', methods: ['GET'])]
    public function show(int $id, EntityManagerInterface $em, SerializerInterface $serializer): JsonResponse
    {
        $vente = $em->getRepository(Vente::class)->find($id);
        if (!$vente) {
            return new JsonResponse(['error' => 'Vente not found'], 404);
        }

        $data = $serializer->serialize($vente, 'json', ['groups' => ['vente']]);
        return new JsonResponse($data, 200, [], true);
    }

    #[Route('', name: 'api_vente_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em, SerializerInterface $serializer): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $vente = new Vente();

        if (!empty($data['adherent_id'])) {
            $adherent = $em->getRepository(Adherent::class)->find($data['adherent_id']);
            if (!$adherent) {
                return new JsonResponse(['error' => 'Adherent not found'], 404);
            }
            $vente->setAdherent($adherent);
        }

        if (!empty($data['tournee_id'])) {
            $tournee = $em->getRepository(Tournee::class)->find($data['tournee_id']);
            if ($tournee) {
                $vente->setTournee($tournee);
            }
        }

        $vente->setMontantTotal($data['montant_total'] ?? 0.0);
        $vente->setMontantPaye($data['montant_paye'] ?? 0.0);
        $vente->setResteAPayer($data['reste_a_payer'] ?? 0.0);
        $vente->setModePaiement($data['mode_paiement'] ?? 'crédit');
        $vente->setDate(new \DateTime($data['date'] ?? 'now'));

        $em->persist($vente);
        $em->flush();

        $json = $serializer->serialize($vente, 'json', ['groups' => ['vente']]);
        return new JsonResponse($json, 201, [], true);
    }

    #[Route('/{id}', name: 'api_vente_update', methods: ['PUT'])]
    public function update(int $id, Request $request, EntityManagerInterface $em, SerializerInterface $serializer): JsonResponse
    {
        $vente = $em->getRepository(Vente::class)->find($id);
        if (!$vente) {
            return new JsonResponse(['error' => 'Vente not found'], 404);
        }

        $data = json_decode($request->getContent(), true);

        $vente->setMontantTotal($data['montant_total'] ?? $vente->getMontantTotal());
        $vente->setMontantPaye($data['montant_paye'] ?? $vente->getMontantPaye());
        $vente->setResteAPayer($data['reste_a_payer'] ?? $vente->getResteAPayer());
        $vente->setModePaiement($data['mode_paiement'] ?? $vente->getModePaiement());

        if (!empty($data['adherent_id'])) {
            $adherent = $em->getRepository(Adherent::class)->find($data['adherent_id']);
            if ($adherent) {
                $vente->setAdherent($adherent);
            }
        }

        if (!empty($data['tournee_id'])) {
            $tournee = $em->getRepository(Tournee::class)->find($data['tournee_id']);
            if ($tournee) {
                $vente->setTournee($tournee);
            }
        }

        $em->flush();

        $json = $serializer->serialize($vente, 'json', ['groups' => ['vente']]);
        return new JsonResponse($json, 200, [], true);
    }

    #[Route('/{id}', name: 'api_vente_delete', methods: ['DELETE'])]
    public function delete(int $id, EntityManagerInterface $em): JsonResponse
    {
        $vente = $em->getRepository(Vente::class)->find($id);
        if (!$vente) {
            return new JsonResponse(['error' => 'Vente not found'], 404);
        }

        $em->remove($vente);
        $em->flush();

        return new JsonResponse(['status' => 'Deleted'], 200);
    }
}