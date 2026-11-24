<?php

declare(strict_types=1);

namespace SightPress\Module\Tests\Fake;

use Nyholm\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use SightPress\Module\Http\AdminScreen;
use SightPress\Module\Http\PublicScreen;
use SightPress\Module\Http\Screens;
use SightPress\Module\Text\Message;

/**
 * Stands in for core's screens in contract tests: remembers what it was asked to render.
 */
final class FakeScreens implements Screens
{
    public AdminScreen|PublicScreen|null $rendered = null;

    public string|Message|null $notice = null;

    public function admin(ServerRequestInterface $request, AdminScreen $screen): ResponseInterface
    {
        $this->rendered = $screen;

        return new Response($screen->status);
    }

    public function public(ServerRequestInterface $request, PublicScreen $screen): ResponseInterface
    {
        $this->rendered = $screen;

        return new Response($screen->status);
    }

    public function redirect(ServerRequestInterface $request, string $path, string|Message|null $notice = null): ResponseInterface
    {
        $this->notice = $notice;

        return new Response(303, ['Location' => $path]);
    }
}
