<?php

namespace App\Controller;

use App\Service\BlacklistService;
use OpenApi\Attributes as OA;
use App\Service\IPInfoChecker;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
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
        private BlacklistService $blacklistService
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

        if ($this->blacklistService->isBlacklisted($ip)) {
            return $this->json(['error' => "IP is blacklisted!"], Response::HTTP_FORBIDDEN);
        }

        $savedIP = $this->ipInfoChecker->checkIpInfo($ip);

        return $this->json($savedIP);
    }


    #[Route('/check/bulk', methods: ['POST'])]
    #[OA\Post(
        path: "/api/check/bulk",
        summary: "Check IP address",
        requestBody: new OA\RequestBody(
            required: true,
            description: 'IPs to check',
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(
                        property: "ips",
                        type: "array",
                        items: new OA\Items(type: "string"),
                        example: ["1.1.1.1", "8.8.8.8"]
                    )
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "IP information retrieved successfully",
                content: new OA\JsonContent(
                    type: "array",
                    items: new OA\Items(properties: [
                        new OA\Property(property: "ip", type: "string", example: "127.0.0.1"),
                        new OA\Property(property: "type", type: "string", example: "ipv4"),
                        new OA\Property(property: "continent_code", type: "string", example: "EU"),
                        new OA\Property(property: "country_code", type: "string", example: "LT"),
                        new OA\Property(property: "region_code", type: "string", example: "VN"),
                        new OA\Property(property: "city", type: "string", example: "Vilnius"),
                        new OA\Property(property: "latitude", type: "number", example: 54.6847),
                        new OA\Property(property: "longitude", type: "number", example: 25.2894),
                    ])
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
            ),
            new OA\Response(
                response: 403,
                description: "Blocked IP addresses",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "error", type: "string", example: "Blocked IP address")
                    ]
                )
            )
        ]
    )]
    public function bulkCheckIPs(Request $request): Response
    {
        $ips = json_decode($request->getContent(), true);

        $ips = $ips['ips'];

        $ip_errors = [];

        foreach ($ips as $ip) {
            $this->validator->validate($ip, new Ip(version: Ip::ALL));
            if ($this->validator->validate($ip, new Ip(version: Ip::ALL))->count() !== 0) {
                $ip_errors[] = $ip;
            }
        }

        if (count($ip_errors) > 0) {
            return $this->json(
                [
                    'error' => 'IPs are invalid',
                    'invalid_ips' => $ip_errors
                ],
                Response::HTTP_BAD_REQUEST
            );
        }



        $savedIPInfos = [];
        $blockedIPs = [];
        foreach ($ips as $ip) {
            //use the manual bulk endpoint, because free API plan only supports that
            $blocked = $this->blacklistService->isBlacklisted($ip);
            if ($blocked) {
                $blockedIPs[] = $ip;
            } else {
                //cache for next call
                $savedIPInfos[]  = $this->ipInfoChecker->checkIpInfo($ip);
            }
        }

        if (count($blockedIPs) > 0) {
            return $this->json(
                [
                    'error' => 'IPs are invalid',
                    'blocked_ips' => $blockedIPs
                ],
                Response::HTTP_FORBIDDEN
            );
        }

        return $this->json($savedIPInfos);
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
                description: "IP information deleted successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "string", example: "OK")
                    ]
                )
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

        $delete_success = $this->ipInfoChecker->deleteSavedIPData($ip);

        if ($delete_success === true) {
            return $this->json(["status" => "OK"]);
        }

        return $this->json(["error" => "not_found"], Response::HTTP_NOT_FOUND);
    }
}
