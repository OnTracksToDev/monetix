<?php

namespace App\Controller\Api;

use App\Entity\Adherent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/api/adherents')]
class AdherentController extends AbstractController
{
    #[Route('', name: 'api_adherent_list', methods: ['GET'])]
    public function list(SerializerInterface $serializer, EntityManagerInterface $em): JsonResponse
    {
        $adherents = $em->getRepository(Adherent::class)->findAll();
        $data = $serializer->serialize($adherents, 'json', ['groups' => ['adherent']]);
        return new JsonResponse($data, 200, [], true);
    }

    #[Route('/{id}', name: 'api_adherent_show', methods: ['GET'])]
    public function show(int $id, SerializerInterface $serializer, EntityManagerInterface $em): JsonResponse
    {
        $adherent = $em->getRepository(Adherent::class)->find($id);
        if (!$adherent) return new JsonResponse(['error' => 'Adherent not found'], 404);
        $data = $serializer->serialize($adherent, 'json', [
            'groups' => ['adherent', 'adherent_details'],
        ]);
        return new JsonResponse($data, 200, [], true);
    }

    #[Route('', name: 'api_adherent_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em, SerializerInterface $serializer): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $adherent = new Adherent();
        $adherent->setNom($data['nom'] ?? '');
        $adherent->setCreditTotal($data['credit_total'] ?? 0.0);

        $em->persist($adherent);
        $em->flush();

        $json = $serializer->serialize($adherent, 'json', ['groups' => ['adherent']]);
        return new JsonResponse($json, 201, [], true);
    }

    #[Route('/{id}', name: 'api_adherent_update', methods: ['PUT'])]
    public function update(int $id, Request $request, EntityManagerInterface $em, SerializerInterface $serializer): JsonResponse
    {
        $adherent = $em->getRepository(Adherent::class)->find($id);
        if (!$adherent) return new JsonResponse(['error' => 'Adherent not found'], 404);

        $data = json_decode($request->getContent(), true);
        $adherent->setNom($data['nom'] ?? $adherent->getNom());
        $adherent->setCreditTotal($data['credit_total'] ?? $adherent->getCreditTotal());

        $em->flush();

        $json = $serializer->serialize($adherent, 'json', ['groups' => ['adherent']]);
        return new JsonResponse($json, 200, [], true);
    }

    #[Route('/{id}', name: 'api_adherent_delete', methods: ['DELETE'])]
    public function delete(int $id, EntityManagerInterface $em): JsonResponse
    {
        $adherent = $em->getRepository(Adherent::class)->find($id);
        if (!$adherent) return new JsonResponse(['error' => 'Adherent not found'], 404);

        $em->remove($adherent);
        $em->flush();

        return new JsonResponse(['status' => 'Deleted'], 200);
    }
}
