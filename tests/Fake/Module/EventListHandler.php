<?php

declare(strict_types=1);

namespace SightPress\Module\Tests\Fake\Module;

use LogicException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class EventListHandler implements RequestHandlerInterface
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        throw new LogicException('Contract tests only register this handler; core runs it.');
    }
}
