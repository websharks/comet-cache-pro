<?php
/*[pro strip-from="lite"]*/
namespace WebSharks\IntelliCache\Pro;

/**
 * Stats Page.
 *
 * @since 151002 Directory stats.
 */
class MenuPageStats extends MenuPage
{
    /**
     * Constructor.
     *
     * @since 151002 Directory stats.
     */
    public function __construct()
    {
        parent::__construct(); // Parent constructor.

        echo '<div id="plugin-menu-page" class="plugin-menu-page">'."\n";

        /* ----------------------------------------------------------------------------------------- */

        echo '<div class="plugin-menu-page-heading">'."\n";

        echo '   <button type="button" class="plugin-menu-page-stats-button" style="float:right;">'.
                    __('Refresh Stats/Charts', SLUG_TD).' <i class="si si-refresh"></i>'.
                 '</button>'."\n";

        echo '   <div class="plugin-menu-page-upsells">'."\n";
        echo '      <a href="'.esc_attr('http://intellicache.me/r/zencache-subscribe/').'" target="_blank"><i class="si si-envelope"></i> '.__('Newsletter', SLUG_TD).'</a>'."\n";
        echo '      <a href="'.esc_attr('http://intellicache.me/r/zencache-beta-testers-list/').'" target="_blank"><i class="si si-envelope"></i> '.__('Beta Testers', SLUG_TD).'</a>'."\n";
        echo '   </div>'."\n";

        if (IS_PRO) {
            echo '<div class="plugin-menu-page-version">'."\n";
            echo '  '.sprintf(__('%1$s&trade; Pro v%2$s', SLUG_TD), esc_html(NAME), esc_html(VERSION))."\n";

            if ($this->plugin->options['latest_pro_version'] && version_compare(VERSION, $this->plugin->options['latest_pro_version'], '<')) {
                echo '(<a href="'.esc_attr(add_query_arg(urlencode_deep(array('page' => GLOBAL_NS.'-pro-updater')), self_admin_url('/admin.php'))).'" style="font-weight:bold;">'.__('update available', SLUG_TD).'</a>)'."\n";
            } else {
                echo '(<a href="'.esc_attr('https://intellicache.me/changelog/').'" target="_blank">'.__('changelog', SLUG_TD).'</a>)'."\n";
            }
            echo '</div>'."\n";
        }
        echo '   <img src="'.$this->plugin->url('/src/client-s/images/stats.png').'" alt="'.esc_attr(__('Statistics', SLUG_TD)).'" />'."\n";

        echo '</div>'."\n";

        /* ----------------------------------------------------------------------------------------- */

        echo '<hr />'."\n";

        /* ----------------------------------------------------------------------------------------- */

        echo '<div class="plugin-menu-page-body">'."\n";

        /* ----------------------------------------------------------------------------------------- */

        echo '  <div class="plugin-menu-page-stats">'."\n";
        echo '      <div class="-wrapper">'."\n";
        echo '          <div class="-container">'."\n";

        echo '              <div class="-refreshing"></div>'."\n";

        echo '              <div class="-totals">'."\n";
        echo '                  <div class="-heading">'.__('Current Cache Totals', SLUG_TD).'</div>'."\n";
        echo '                  <div class="-files"><span class="-value">&nbsp;</span></div>'."\n";
        echo '                  <div class="-size"><span class="-value">&nbsp;</span></div>'."\n";
        echo '                  <div class="-dir">'.esc_html(basename(WP_CONTENT_DIR).'/'.$this->plugin->options['base_dir'].'/*').'</div>'."\n";
        echo '              </div>'."\n";

        echo '              <div class="-disk">'."\n";
        echo '                  <div class="-heading">'.__('Current Disk Health', SLUG_TD).'</div>'."\n";
        echo '                  <div class="-size"><span class="-value">&nbsp;</span> '.__('total capacity', SLUG_TD).'</div>'."\n";
        echo '                  <div class="-free"><span class="-value">&nbsp;</span> '.__('available', SLUG_TD).'</div>'."\n";
        echo '              </div>'."\n";

        echo '              <div class="-system">'."\n";
        echo '                  <div class="-heading">'.__('Current System Health', SLUG_TD).'</div>'."\n";
        echo '                  <div class="-memory-usage">'.__('Memory Usage:', SLUG_TD).' <span class="-value">&nbsp;</span></div>'."\n";
        echo '                  <div class="-load-average">'.__('Load Average:', SLUG_TD).' <span class="-value">&nbsp;</span></div>'."\n";
        echo '              </div>'."\n";

        echo '              <div class="-chart-divider"></div>'."\n";

        echo '              <div class="-chart-a">'."\n";
        echo '                  <div class="-heading">'.__('Cache File Counts', SLUG_TD).'</div>'."\n";
        echo '                  <canvas class="-canvas"></canvas>'."\n";
        echo '                  <div class="-empty"></div>'."\n";
        echo '              </div>'."\n";

        echo '              <div class="-chart-b">'."\n";
        echo '                  <div class="-heading">'.__('Cache File Sizes', SLUG_TD).'</div>'."\n";
        echo '                  <canvas class="-canvas"></canvas>'."\n";
        echo '                  <div class="-empty"></div>'."\n";
        echo '              </div>'."\n";

        echo '              <div class="-chart-divider"></div>'."\n";

        echo '              <div class="-opcache">'."\n";
        echo '                  <div class="-memory">'."\n";
        echo '                      <div class="-heading">'.__('OPcache Memory', SLUG_TD).'</div>'."\n";
        echo '                      <div class="-free"><span class="-value">&nbsp;</span> '.__('free', SLUG_TD).'</div>'."\n";
        echo '                      <div class="-used"><span class="-value">&nbsp;</span> '.__('used', SLUG_TD).'</div>'."\n";
        echo '                      <div class="-wasted"><span class="-value">&nbsp;</span> '.__('wasted', SLUG_TD).'</div>'."\n";
        echo '                  </div>'."\n";

        echo '                  <div class="-totals">'."\n";
        echo '                      <div class="-heading">'.__('OPcache Totals', SLUG_TD).'</div>'."\n";
        echo '                      <div class="-scripts"><span class="-value">&nbsp;</span> '.__('cached scripts', SLUG_TD).'</div>'."\n";
        echo '                      <div class="-keys"><span class="-value">&nbsp;</span> '.__('total cached keys', SLUG_TD).'</div>'."\n";
        echo '                  </div>'."\n";

        echo '                  <div class="-hits-misses">'."\n";
        echo '                      <div class="-heading">'.__('OPcache Hits/Misses', SLUG_TD).'</div>'."\n";
        echo '                      <div class="-hits"><span class="-value">&nbsp;</span> '.__('hits', SLUG_TD).'</div>'."\n";
        echo '                      <div class="-misses"><span class="-value">&nbsp;</span> '.__('misses', SLUG_TD).'</div>'."\n";
        echo '                      <div class="-hit-rate"><span class="-value">&nbsp;</span> '.__('hit rate', SLUG_TD).'</div>'."\n";
        echo '                  </div>'."\n";
        echo '              </div>'."\n";

        echo '          </div>'."\n";
        echo '      </div>'."\n";
        echo '  </div>'."\n";

        /* ----------------------------------------------------------------------------------------- */

        echo '</div>'."\n";
        echo '</div>';
    }
}
/*[/pro]*/
