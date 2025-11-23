<?php

namespace App\Controller\Api;

use App\Entity\Tournee;
use App\Entity\Adherent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/api/tournees')]
class TourneeController extends AbstractController
{
    #[Route('', name: 'api_tournee_list', methods: ['GET'])]
    public function list(EntityManagerInterface $em, SerializerInterface $serializer): JsonResponse
    {
        $tournees = $em->getRepository(Tournee::class)->findAll();
        $data = $serializer->serialize($tournees, 'json', ['groups' => ['tournee','tournee_details']]);
        return new JsonResponse($data, 200, [], true);
    }

    #[Route('/{id}', name: 'api_tournee_show', methods: ['GET'])]
    public function show(int $id, EntityManagerInterface $em, SerializerInterface $serializer): JsonResponse
    {
        $tournee = $em->getRepository(Tournee::class)->find($id);
        if (!$tournee) {
            return new JsonResponse(['error' => 'Tournee not found'], 404);
        }

        $data = $serializer->serialize($tournee, 'json', ['groups' => ['tournee']]);
        return new JsonResponse($data, 200, [], true);
    }

    #[Route('/{id}/ventes', name: 'api_tournee_ventes', methods: ['GET'])]
    public function getVentes(int $id, EntityManagerInterface $em, SerializerInterface $serializer): JsonResponse
    {
        $tournee = $em->getRepository(Tournee::class)->find($id);
        if (!$tournee) {
            return new JsonResponse(['error' => 'Tournee not found'], 404);
        }

        $ventes = $tournee->getVentes();
        $data = $serializer->serialize($ventes, 'json', [
            'groups' => ['vente']
        ]);
        return new JsonResponse($data, 200, [], true);
    }

    #[Route('', name: 'api_tournee_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em, SerializerInterface $serializer): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $tournee = new Tournee();

        if (!empty($data['adherent_id'])) {
            $adherent = $em->getRepository(Adherent::class)->find($data['adherent_id']);
            if (!$adherent) {
                return new JsonResponse(['error' => 'Adherent not found'], 404);
            }
            $tournee->setAdherent($adherent);
        }

        $tournee->setMontantTotal($data['montant_total'] ?? 0.0);
        $tournee->setMontantPaye($data['montant_paye'] ?? 0.0);
        $tournee->setResteAPayer($data['reste_a_payer'] ?? 0.0);
        $tournee->setEstimationClients($data['estimation_clients'] ?? null);
        $tournee->setDateDebut(new \DateTime($data['date_debut'] ?? 'now'));
        $tournee->setDateFin(!empty($data['date_fin']) ? new \DateTime($data['date_fin']) : null);
        $tournee->setStatut($data['statut'] ?? 'en_cours');

        $em->persist($tournee);
        $em->flush();

        $json = $serializer->serialize($tournee, 'json', ['groups' => ['tournee']]);
        return new JsonResponse($json, 201, [], true);
    }

    #[Route('/{id}', name: 'api_tournee_update', methods: ['PUT'])]
    public function update(int $id, Request $request, EntityManagerInterface $em, SerializerInterface $serializer): JsonResponse
    {
        $tournee = $em->getRepository(Tournee::class)->find($id);
        if (!$tournee) {
            return new JsonResponse(['error' => 'Tournee not found'], 404);
        }

        $data = json_decode($request->getContent(), true);

        $tournee->setMontantTotal($data['montant_total'] ?? $tournee->getMontantTotal());
        $tournee->setMontantPaye($data['montant_paye'] ?? $tournee->getMontantPaye());
        $tournee->setResteAPayer($data['reste_a_payer'] ?? $tournee->getResteAPayer());
        $tournee->setDateDebut(!empty($data['date_debut']) ? new \DateTime($data['date_debut']) : $tournee->getDateDebut());
        $tournee->setDateFin(!empty($data['date_fin']) ? new \DateTime($data['date_fin']) : $tournee->getDateFin());
        $tournee->setStatut($data['statut'] ?? $tournee->getStatut());

        if (!empty($data['adherent_id'])) {
            $adherent = $em->getRepository(Adherent::class)->find($data['adherent_id']);
            if ($adherent) {
                $tournee->setAdherent($adherent);
            }
        }

        $em->flush();

        $json = $serializer->serialize($tournee, 'json', ['groups' => ['tournee']]);
        return new JsonResponse($json, 200, [], true);
    }

    #[Route('/{id}', name: 'api_tournee_delete', methods: ['DELETE'])]
    public function delete(int $id, EntityManagerInterface $em): JsonResponse
    {
        $tournee = $em->getRepository(Tournee::class)->find($id);
        if (!$tournee) {
            return new JsonResponse(['error' => 'Tournee not found'], 404);
        }

        $em->remove($tournee);
        $em->flush();

        return new JsonResponse(['status' => 'Deleted'], 200);
    }
}
