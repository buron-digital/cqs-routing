<?php

declare(strict_types=1);

namespace BuronDigital\CQSRouting\Test\Domain\Tasks\WriteSide\CreateTask;

use BuronDigital\CQSRouting\RequestDecoder\RequestDecoder;
use Symfony\Component\HttpFoundation\Request;

final class CreateTaskRequestDecoder implements RequestDecoder
{
    #[\Override]
    public function decodeRequest(Request $request): array
    {
        return [
            'title' => $request->request->get('title'),
            'content' => $request->request->get('content'),
            'priority' => $request->request->get('priority'),
        ];
    }
}
