<?php

namespace App\Controller;

use OpenApi\Attributes as OA;
use App\Service\BlacklistService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Constraints\Ip;


#[Route('/api/blacklist')]
class BlacklistController extends AbstractController
{

    public function __construct(
        private BlacklistService $blacklistService,
        private ValidatorInterface $validator

    ) {}

    #[Route('/', methods: ['POST'])]
    #[
        OA\Post(
            path: "/api/blacklist/",
            summary: "Blacklist IP",
            requestBody: new OA\RequestBody(
                required: true,
                description: 'IP to blacklist',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "ip", type: "string", example: "127.0.0.1")
                    ]
                )
            ),
            responses: [
                new OA\Response(
                    response: 200,
                    description: "IP is blacklisted",
                    content: new OA\JsonContent(
                        properties: [
                            new OA\Property(property: "status", type: "string", example: "OK")
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
        )
    ]
    public function blacklistIp(Request $request): Response
    {
        $json_content = json_decode($request->getContent(), true);

        $ip = $json_content["ip"];

        $errors = $this->validator->validate($ip, new Ip(version: Ip::ALL));
        if ($errors->count() > 0) {
            return $this->json(['error' => 'Invalid IP address'], Response::HTTP_BAD_REQUEST);
        }


        $this->blacklistService->blacklistIP($ip);

        return $this->json(["status" => "OK"]);
    }


    /**
     * 
     * @param array $ips
     * @return void 
     */

    #[Route('/bulk', methods: ['POST'])]
    #[
        OA\Post(
            path: "/api/blacklist/bulk",
            summary: "Bulk blacklist IPs",
            requestBody: new OA\RequestBody(
                required: true,
                description: 'IPs to blacklist',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: "ips",
                            type: "array",
                            items: new OA\Items(type: "string"),
                            example: ["127.0.0.1", "192.168.0.1"]
                        )
                    ]
                )
            ),
            responses: [
                new OA\Response(
                    response: 200,
                    description: "IPs are blacklist",
                    content: new OA\JsonContent(
                        properties: [
                            new OA\Property(property: "status", type: "string", example: "OK
")
                        ]
                    )
                ),
                new OA\Response(
                    response: 400,
                    description: "One or more IPs are invalid",
                    content: new OA\JsonContent(
                        properties: [
                            new OA\Property(property: "error", type: "string", example: "IPsare invalid")
                        ]
                    )
                )
            ]
        )
    ]
    public function bulkBlacklistIPs(Request $request): Response
    {
        $ips = json_decode($request->getContent(), true);

        $ips = $ips['ips'] ?? [];

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

        foreach ($ips as $ip) {
            $this->blacklistService->blacklistIP($ip);
        }
        return $this->json(["status" => "OK"]);
    }



    /**
     *    
     * @param string $ip
     * @return Response 
     */
    #[OA\Delete(
        path: "/api/blacklist/{ip}",
        summary: "Remove IP from blacklist",
        responses: [
            new OA\Response(
                response: 200,
                description: "IP is removed from the blacklist",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "string", example: "OK")
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: "IP address not found",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "error", type: "string", example: "Not found")
                    ]
                )
            )
        ]
    )]
    #[Route('/{ip}', methods: ['DELETE'])]
    public function deleteBlacklistedIP(string $ip)
    {
        //no need to validate it since we can just return a 404 instead
        $unblocked = $this->blacklistService->unblockIP($ip);

        if ($unblocked === false) {
            return $this->json(['error' => 'Not found'], Response::HTTP_NOT_FOUND);
        }

        return $this->json(['status' => 'OK']);
    }
}
