(function ($) {

  Drupal.behaviors.smartlingCheckAll = {
    attach: function (context, settings) {
      if (settings.smartling != undefined && settings.smartling.checkAllId != undefined) {
        $.each(settings.smartling.checkAllId, function (index, id) {
          var $checkboxWrapper = $(id, context),
            $checkboxesLink = $('#smartling-check-all-' + index, context);

          if ($checkboxesLink.length || $checkboxWrapper.length < 1) {
            return;
          }
          if ($checkboxWrapper.children().length > 5) {
            $checkboxWrapper.addClass('big-select-languages-widget');
          }

          $checkboxWrapper.prepend('<a href="#" id="smartling-check-all-' + index + '">' + Drupal.t('Check/uncheck all') + '</a>');
          $('#smartling-check-all-' + index, context).click({checkboxWrapper: $checkboxWrapper}, function (e) {
            var $_this = $(this);
            $_this.toggleClass('checked');
            $checkboxWrapper.find(':checkbox').each(function () {
              if (!!$_this.hasClass('checked')) {
                $(this).filter(':checked').click();
              }
              else {
                $(this).filter(':not(:checked)').click();
              }
            });
            e.preventDefault();
            });
        });
      }
    }
  };

})(jQuery);
