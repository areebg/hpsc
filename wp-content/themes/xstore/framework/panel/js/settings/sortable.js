/**
 * Description
 *
 * @package    sortable.js
 * @since      1.0.0
 * @version    1.0.1
 * @author     Stas
 * @link       http://xstore.8theme.com
 * @license    Themeforest Split Licence
 */
jQuery(document).ready(function ($) {
    var templateElements = $('.xstore-panel-sortable .xstore-panel-sortable-items, .xstore-panel-repeater .xstore-panel-sortable-items');
    templateElements.sortable({
        items: '.sortable-item',
    });
    $(document).on('et_panel_popup_loaded', function (e, popup) {
        popup.find('.xstore-panel-sortable .xstore-panel-sortable-items, .xstore-panel-repeater .xstore-panel-sortable-items').sortable({
            items: '.sortable-item',
        });
    });
    $(document).on('click', '.sortable-item:not(.no-settings, .disabled) .sortable-item-title', function () {
       $(this).parent().toggleClass('opened');
    });
    $(document).on('change', '.sortable-item-title .item-visibility input', function () {
        $(this).parent().parent().parent().toggleClass('disabled');
    });
} );
