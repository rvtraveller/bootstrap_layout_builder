/**
 * @file
 * Behaviors Bootstrap Layout Builder general scripts.
 */

(function ($, _, Drupal, drupalSettings) {
  "use strict";
  
  // Configure Section.
  Drupal.behaviors.bootstrapLayoutBuilderConfigureSection = {
    attach: function (context) {

      // Our tabbed UI
      $(".blb_nav-tabs li a", context).once('blb_nav-tabs').on('click', function () {
        $('.blb_nav-tabs li a').removeClass('active');
        $(this).toggleClass('active');
        var href = $(this).attr('data-target');


        if(href && $('.blb_tab-content').length) {
          $('.blb_tab-pane').removeClass('active');
          $('.blb_tab-pane--' + href).addClass('active');
        }
      });

      $(".bootstrap_layout_builder_bg_color input:radio").each(function () {
        $(this).next('label').addClass($(this).val());
      });

    }
  };

})(window.jQuery, window._, window.Drupal, window.drupalSettings);
