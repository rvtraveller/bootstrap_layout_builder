/**
 * @file
 * Behaviors Bootstrap Layout Builder general scripts.
 */

(function ($, _, Drupal, drupalSettings) {
  "use strict";
  
  // Configure Section.
  Drupal.behaviors.bootstrapLayoutBuilderConfigureSection = {
    attach: function (context) {

      $(".bootstrap_layout_builder_bg_color input:radio").each(function () {
        $(this).next('label').addClass($(this).val());
      });

    }
  };

})(window.jQuery, window._, window.Drupal, window.drupalSettings);
