<?php

declare(strict_types=1);

namespace App\Tests\Services\Asserter\ResponseAsserter;

use Symfony\Component\HttpFoundation\JsonResponse;

class JsonResponseAsserter extends ResponseAsserter
{
    public const IGNORE_VALUE = null;

    public static function create(): self
    {
        return (new self())
            ->withExpectedClass(JsonResponse::class)
            ->addHeaderAsserter(new HeaderAsserter([
                'content-type' => 'application/json'
            ]))
        ;
    }
}
