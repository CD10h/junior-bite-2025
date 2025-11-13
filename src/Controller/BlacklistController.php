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
     * @param string $ip
     * @return Response 
     */
    #[OA\Post(
        path: "/api/blacklist/{ip}",
        summary: "Blacklist IP",
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
    public function unblockIP(string $ip)
    {
        //no need to validate it since we can just return a 404 instead
        $unblocked = $this->blacklistService->unblockIP($ip);

        if ($unblocked === false) {
            return $this->json(['error' => 'Not found'], Response::HTTP_NOT_FOUND);
        }

        return $this->json(['status' => 'OK']);
    }
}
