<?php

declare(strict_types=1);

namespace App\Response;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class BadRequestResponse extends JsonResponse
{
    /**
     * @param array<string, mixed> $context
     */
    public function __construct(string $message, array $context = [])
    {
        parent::__construct(
            array_merge(
                [
                    'message' => $message,
                ],
                $context,
            ),
            Response::HTTP_BAD_REQUEST,
            [],
            false
        );
    }
}
