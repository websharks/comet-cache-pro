<?php
namespace WebSharks\CometCache\Pro\Classes;

/**
 * Menu Page.
 *
 * @since 150422 Rewrite.
 */
class MenuPage extends AbsBase
{
    /**
     * Constructor.
     *
     * @since 150422 Rewrite.
     *
     * @param string $menu_page Menu page.
     */
    public function __construct($menu_page = '')
    {
        parent::__construct();

        switch ($menu_page) {
            case 'options':
                new MenuPageOptions();
                break;

            /*[pro strip-from="lite"]*/
            case 'stats':
                new MenuPageStats();
                break;
            /*[/pro]*/
        }
    }
}
