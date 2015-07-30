(function ($) {
  'use strict';
  Drupal.behaviors.smartlingProgressbar = {
    attach: function (context, settings) {

      var progress = '';
      var is_progressbar = $('.view-smartling-submissions .views-field-progress').attr('class');
      if (typeof is_progressbar !== 'undefined') {
        $('.view-smartling-submissions tbody .views-field-progress').each(function () {
          progress = $(this).html();
          var progress_string = '<div class="progress-val">' + progress + '</div>';
          $(this).empty();
          $(this).append(progress_string);
          $(this).css({'display': 'block', 'position': 'relative'});
          $(this).find('.progress-val').css({
            'display': 'inline-block',
            'width': '100%',
            'text-align': 'center',
            'position': 'absolute',
            'left': '0'
          });
          $(this).progressbar({
            value: parseInt(progress)
          });
        });
      }
      var is_title = $('.view-smartling-submissions table.views-table').attr('class');
      if (typeof is_title !== 'undefined') {
        $('.views-field-smartling-title').each(function () {
          $(this).css({'width': '40%'});
        });
        $('.views-field-rid').each(function () {
          $(this).css({'width': '4%'});
        });
        $('.views-field-name').each(function () {
          $(this).css({'width': '4%'});
        });
      }
    }
  };

  Drupal.behaviors.smartlingConfirmDelete = {
    attach: function (context, settings) {
      var button = $('.confirm-delete-ajax-submit');
      button.click(function () {
        show_confirmation();
      });

      function show_confirmation() {
        if (confirm("Do you want to submit?")) {
          return true;
        } else {
          // return false prevents the form from submitting
          return false;
        }
      }
    }
  };

  Drupal.behaviors.smartlingTargetLocales = {
    attach: function (context, settings) {
      var languagesList = [],
        $targetLanguageWrapper = $('#edit-target-locales', context);
      $targetLanguageWrapper.find('.form-type-checkbox').each(function () {
        var languageCode = $(this).find(':input').attr('value');
        languagesList.push(languageCode);
      });

      $.each(languagesList, function (index, lang) {
        $('#edit-target-locales-text-keys-' + lang).appendTo($targetLanguageWrapper.find('.form-item-target-locales-' + lang));
      });
    }
  };

})(jQuery);
