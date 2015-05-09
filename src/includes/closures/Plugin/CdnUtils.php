<?php
namespace WebSharks\ZenCache\Pro;

/*
 * Bumps CDN invalidation counter.
 *
 * @since 150422 Rewrite.
 */
$self->bumpCdnInvalidationCounter = function () use ($self) {
    if (!$self->options['enable']) {
        return; // Nothing to do.
    }
    if (!$self->options['cdn_enable']) {
        return; // Nothing to do.
    }
    $self->options['cdn_invalidation_counter'] = // Bump!
        (string) ($self->options['cdn_invalidation_counter'] + 1);

    update_option(GLOBAL_NS.'_options', $self->options);
    if (is_multisite()) {
        update_site_option(GLOBAL_NS.'_options', $self->options);
    }
};