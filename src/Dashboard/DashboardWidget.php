<?php

declare(strict_types=1);

namespace SightPress\Module\Dashboard;

/**
 * A box on the admin dashboard, for example "Last 7 days" from analytics. Modules add widgets with
 * `ModuleRegistrar::dashboard()->add()`; core builds them from the site's container.
 *
 * Core renders each widget as a `<section>` labelled by an `<h2>` with the {@see title()}, in {@see position()}
 * order, and leaves out widgets whose {@see permission()} the user doesn't hold and widgets of modules that are
 * off for the site.
 */
interface DashboardWidget
{
    /**
     * Unique id: lower-case words joined by dots, starting with the module's area, for example `analytics.summary`.
     *
     * @return non-empty-string
     */
    public function id(): string;

    /**
     * Heading text in en-GB, for example "Last 7 days"; core translates it with the module's translations. It must
     * make sense on its own, because screen reader users move between the dashboard's headings.
     *
     * @return non-empty-string
     */
    public function title(): string;

    /**
     * Permission needed to see the widget, for example `analytics.view`; null shows it to every admin user.
     *
     * @return non-empty-string|null
     */
    public function permission(): ?string;

    /**
     * Lower comes first; widgets with the same position are sorted by title.
     */
    public function position(): int;

    /**
     * The widget's content (HTML), without the heading. Must meet WCAG 2.2 AA, escape every value, use headings
     * from `<h3>` down, give a text alternative for every chart, work without JavaScript and show only what the
     * user may see. It runs on every dashboard view, so it must be quick: read prepared figures, never call
     * another service over the network.
     */
    public function render(DashboardContext $context): string;
}
