/**
 * @file
 * Custom javascript.
 */

(function($) {

  Drupal.behaviors.smartlingProgressbar = {
    attach: function(context, settings) {

      var progress = '';
      var is_progressbar = $('.view-smartlig-report .views-field-progress').attr('class');
      if (typeof is_progressbar !== 'undefined') {
        $('.view-smartlig-report tbody .views-field-progress').each(function() {
          progress = $(this).html();
          var progress_string = '<div class="progress-val">' + progress + '</div>';
          $(this).empty();
          $(this).append(progress_string);
          $(this).css({'display': 'block', 'position': 'relative'});
          $(this).find('.progress-val').css({'display': 'inline-block', 'width': '100%', 'text-align': 'center', 'position': 'absolute', 'left': '0'});
          $(this).progressbar({
            value: parseInt(progress)
          });
        });
      }
    }
  }

  Drupal.behaviors.smartlingConfirmDelete = {
    attach: function(context, settings) {
      var button = $('.confirm-delete-ajax-submit');
      button.click(function() {
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
  }

  Drupal.behaviors.smartlingTargetLocales = {
    attach: function(context, settings) {
      var langList = new Array();
      var lang = '';
      var originClass = '';
      var replaceText = 'form-item form-type-checkbox form-item-target-locales-';
      $('#edit-target-locales .form-type-checkbox').each(function() {
        originClass = $(this).attr("class");
        lang = originClass.replace(replaceText, '');
        langList.push(lang);
      });
      console.log(langList);
      $.each(langList, function(index, lang) {
        var element = $('.wrap-target-locales-text-key .form-item-target-locales-text-key-' + lang);
        $('#edit-target-locales .form-item-target-locales-' + lang).append(element);
      });
    }
  }


})(jQuery);
