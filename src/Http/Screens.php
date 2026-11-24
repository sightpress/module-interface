<?php

declare(strict_types=1);

namespace SightPress\Module\Http;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use SightPress\Module\Text\Message;

/**
 * Renders a module's screens the way core renders its own, so they share the admin panel or the site's theme, its
 * accessibility features and its security headers. Get it from the site's container (`Screens::class`) in a
 * request handler.
 *
 * Every `POST` form must print `{{ csrf_fields() }}`: core refuses a `POST` without a valid token before the
 * handler runs. Implemented by core only, so methods can be added in a minor release.
 */
interface Screens
{
    /**
     * An admin screen. The route should have a `permission` ({@see \SightPress\Module\Routing\Route}), so only
     * people allowed to see it get here.
     */
    public function admin(ServerRequestInterface $request, AdminScreen $screen): ResponseInterface;

    /**
     * A page of the public site, in the site's theme and in the language of the request.
     */
    public function public(ServerRequestInterface $request, PublicScreen $screen): ResponseInterface;

    /**
     * Sends the browser to a site-relative path with a `303 See Other` (after a form was saved), with an optional
     * notice that the next screen shows once, in a status message screen readers announce.
     *
     * @param string $path starts with `/`, for example `/admin/events`
     */
    public function redirect(ServerRequestInterface $request, string $path, string|Message|null $notice = null): ResponseInterface;
}
