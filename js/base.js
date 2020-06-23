/**
 * @file
 * Behaviors Bootstrap Layout Builder general scripts.
 */

(function ($, _, Drupal, drupalSettings) {
  "use strict";
  
  // Configure Section.
  Drupal.behaviors.bootstrapLayoutBuilderConfigureSection = {
    attach: function (context) {

      // Our Tabbed User Interface.
      $(".blb_nav-tabs li a", context).once('blb_nav-tabs').on('click', function () {
        $('.blb_nav-tabs li a').removeClass('active');
        $(this).toggleClass('active');
        var href = $(this).attr('data-target');

        if(href && $('.blb_tab-content').length) {
          $('.blb_tab-pane').removeClass('active');
          $('.blb_tab-pane--' + href).addClass('active');
        }
      });

      // Graphical Layout Columns
      $(".blb_breakpoint_cols").each(function () {
        const numOfCols = 12;

        $(this).find('.form-item').each(function () {
          var cols = $(this).find('input').val().replace('blb_col_', '');
          var colsConfig = cols.split('_');
          var colsLabel = $(this).find('label');

          colsLabel.wrapInner('<div class="blb_tooltip blb_tooltip-lg"></div>');

          $.each(colsConfig, function(index, value) {
            var width = ((value / numOfCols) * 100);

            $('<div />', {
              'text': width.toFixed(0) + '%',
              'style': 'width:' + width + '%;',
              'class': 'blb_breakpoint_col'
            }).appendTo(colsLabel);
          });

          console.log(colsConfig);
        });

      });

      $(".bootstrap_layout_builder_bg_color input:radio", context).once('blb_bg-color').each(function () {
        $(this).next('label').addClass($(this).val());
      });

    }
  };

})(window.jQuery, window._, window.Drupal, window.drupalSettings);
