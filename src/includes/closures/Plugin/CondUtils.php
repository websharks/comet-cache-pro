<?php
namespace WebSharks\IntelliCache\Pro;

/*
 * Is pro preview?
 *
 * @since 150511 Rewrite.
 *
 * @return bool `TRUE` if it's a pro preview.
 */
$self->isProPreview = function () use ($self) {
    return !empty($_REQUEST[GLOBAL_NS.'_pro_preview']);
};
