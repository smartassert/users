<?php

declare(strict_types=1);

namespace App\Tests\Services\Asserter\ResponseAsserter;

use Symfony\Component\HttpFoundation\Response;

class TextPlainResponseAsserter extends ResponseAsserter
{
    public function __construct(int $expectedStatusCode)
    {
        parent::__construct($expectedStatusCode, Response::class);

        $this->addHeaderAsserter(new HeaderAsserter([
            'content-type' => 'text/plain; charset=UTF-8'
        ]));
    }
}
