<?php

declare(strict_types=1);

namespace App\Tests\Services\Asserter\ResponseAsserter;

use Symfony\Component\HttpFoundation\JsonResponse;

class JsonResponseAsserter extends ResponseAsserter
{
    public const IGNORE_VALUE = null;

    public function __construct(int $expectedStatusCode)
    {
        parent::__construct($expectedStatusCode, JsonResponse::class);

        $this->addHeaderAsserter(new HeaderAsserter([
            'content-type' => 'application/json'
        ]));
    }
}
