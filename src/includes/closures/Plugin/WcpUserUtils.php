<?php
/*[pro strip-from="lite"]*/
namespace WebSharks\IntelliCache\Pro;

/*
 * Clears cache files associated with a particular user.
 *
 * @since 150422 Rewrite.
 *
 * @attaches-to `profile_update` hook.
 * @attaches-to `add_user_metadata` filter.
 * @attaches-to `updated_user_meta` hook.
 * @attaches-to `delete_user_metadata` filter.
 * @attaches-to `set_auth_cookie` hook.
 * @attaches-to `clear_auth_cookie` hook.
 *
 * @param int $user_id A WordPress user ID.
 *
 * @return int Total files cleared.
 */
$self->autoClearUserCache = function ($user_id) use ($self) {
    $counter = 0; // Initialize.

    if (!($user_id = (integer) $user_id)) {
        return $counter; // Nothing to do.
    }
    if (!is_null($done = &$self->cacheKey('autoClearUserCache', $user_id))) {
        return $counter; // Already did this.
    }
    $done = true; // Flag as having been done.

    if (!$self->options['enable']) {
        return $counter; // Nothing to do.
    }
    if ($self->options['when_logged_in'] !== 'postload') {
        return $counter; // Nothing to do.
    }
    $regex = $self->assembleCachePathRegex('', '.*?\.u\/'.preg_quote($user_id, '/').'[.\/]');
    $counter += $self->wipeFilesFromCacheDir($regex); // Clear matching files.

    if ($counter && is_admin() && (!IS_PRO || $self->options['change_notifications_enable'])) {
        $self->enqueueNotice('<img src="'.esc_attr($self->url('/src/client-s/images/clear.png')).'" style="float:left; margin:0 10px 0 0; border:0;" />'.
                              sprintf(__('<strong>%1$s:</strong> detected changes. Found %2$s in the cache for user ID: <code>%3$s</code>; auto-clearing.', SLUG_TD), esc_html(NAME), esc_html($self->i18nFiles($counter)), esc_html($user_id)));
    }
    return $counter;
};
$self->auto_clear_user_cache = $self->autoClearUserCache; // Back compat.

/*
 * Automatically clears cache files associated with a particular user.
 *
 * @since 150422 Rewrite.
 *
 * @attaches-to `profile_update` hook.
 *
 * @param int $user_id A WordPress user ID.
 */
$self->autoClearUserCacheA1 = function ($user_id) use ($self) {
    $self->autoClearUserCache($user_id);
};

/*
* Automatically clears cache files associated with a particular user.
*
* @since 151220 Using `updated_user_meta` instead of `update_user_metadata`
*
* @attaches-to `updated_user_meta` hook.
*
* @param int    $meta_id    ID of updated metadata entry.
* @param int    $object_id  Object ID.
*/
$self->autoClearUserCacheA2 = function ($meta_id, $object_id) use ($self) {
    $self->autoClearUserCache($object_id);
};

/*
 * Automatically clears cache files associated with a particular user.
 *
 * @since 150422 Rewrite.
 *
 * @attaches-to `add_user_metadata` filter.
 * @attaches-to `updated_user_meta` hook.
 * @attaches-to `delete_user_metadata` filter.
 *
 * @param mixed $value   Filter value (passes through).
 * @param int   $user_id A WordPress user ID.
 *
 * @return mixed The same `$value` (passes through).
 */
$self->autoClearUserCacheFA2 = function ($value, $user_id) use ($self) {
    $self->autoClearUserCache($user_id);
    return $value; // Filter.
};

/*
 * Automatically clears cache files associated with a particular user.
 *
 * @since 150422 Rewrite.
 *
 * @attaches-to `set_auth_cookie` hook.
 *
 * @param mixed $_       Irrelevant hook argument value.
 * @param mixed $__      Irrelevant hook argument value.
 * @param mixed $___     Irrelevant hook argument value.
 * @param int   $user_id A WordPress user ID.
 */
$self->autoClearUserCacheA4 = function ($_, $__, $___, $user_id) use ($self) {
    $self->autoClearUserCache($user_id);
};

/*
 * Automatically clears cache files associated with current user.
 *
 * @since 150422 Rewrite.
 *
 * @attaches-to `clear_auth_cookie` hook.
 */
$self->autoClearUserCacheCur = function () use ($self) {
    $self->autoClearUserCache(get_current_user_id());
};
$self->auto_clear_user_cache_cur = $self->autoClearUserCacheCur; // Back compat.
/*[/pro]*/
