<?php

namespace App\Controller;

use App\Repository\SavedIPRepository;
use OpenApi\Attributes as OA;
use App\Service\IPInfoChecker;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Constraints\Ip;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api')]
class IpCheckController extends AbstractController
{

    public function __construct(
        private EntityManagerInterface $entityManager,
        private ValidatorInterface $validator,
        private IPInfoChecker $ipInfoChecker,
        private SavedIPRepository $ipRepository


    ) {}

    /**
     * Method to check an IP address and return its information.
     * 
     * @param string $ip
     * @return Response 
     */
    #[OA\Get(
        path: "/api/check/{ip}",
        summary: "Check IP address",
        responses: [
            new OA\Response(
                response: 200,
                description: "IP information retrieved successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "ip", type: "string", example: "127.0.0.1"),
                        new OA\Property(property: "type", type: "string", example: "ipv4"),
                        new OA\Property(property: "continent_code", type: "string", example: "EU"),
                        new OA\Property(property: "country_code", type: "string", example: "LT"),
                        new OA\Property(property: "region_code", type: "string", example: "VN"),
                        new OA\Property(property: "city", type: "string", example: "Vilnius"),
                        new OA\Property(property: "latitude", type: "number", example: 54.6847),
                        new OA\Property(property: "longitude", type: "number", example: 25.2894),
                    ]
                )
            ),
            new OA\Response(
                response: 400,
                description: "Invalid IP address",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "error", type: "string", example: "Invalid IP address")
                    ]
                )
            )
        ]
    )]
    #[Route(path: '/check/{ip}', methods: ['GET'])]
    public function checkIP(string $ip): Response
    {
        //Validate to save API calls

        $errors = $this->validator->validate($ip, new Ip(version: Ip::ALL));
        if ($errors->count() > 0) {
            return $this->json(['error' => 'Invalid IP address'], Response::HTTP_BAD_REQUEST);
        }

        $savedIP = $this->ipInfoChecker->checkIpInfo($ip);

        return $this->json($savedIP);
    }

    /**
     * Delete cached IP information.
     * 
     * @param string $ip
     * @return Response 
     */
    #[OA\Delete(
        path: "/api/check/{ip}",
        summary: "Delete cached IP information",
        responses: [
            new OA\Response(
                response: 200,
                description: "IP information deleted successfully"
            ),
            new OA\Response(
                response: 404,
                description: "IP not found"
            )
        ]
    )]
    #[Route(path: '/check/{ip}', methods: ['DELETE'])]
    public function deleteIP(string $ip): Response
    {

        $ipInfo = $this->ipRepository->findOneByIp($ip);

        if (!$ipInfo) {
            return new Response('', Response::HTTP_NOT_FOUND);
        }

        $this->entityManager->remove($ipInfo);
        $this->entityManager->flush();

        return new Response('', Response::HTTP_OK);
    }
}
