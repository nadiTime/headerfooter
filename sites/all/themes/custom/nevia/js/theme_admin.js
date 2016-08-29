(function($) {

  $(document).ready(function() {

   // var drupal_base_path = Drupal.settings.basePath;

 

    $('.color').after(('<div class="ls-colorpicker" />'));

    jQuery('.ls-colorpicker').each(function() {
      var $item = $(this);

      $item.farbtastic(function(color) {

        // Set color code in the input
        // jQuery('.color').val(color);

        $item.parent('.form-item.form-type-textfield').find('.color').val(color);

        // Set input background
        //jQuery('.color').css('background-color', color);
        $item.parent('.form-item.form-type-textfield').find('.color').css('background-color', color);

        // Update preview

      }).hide();
    });

    // Show color picker on focus
    jQuery('.color').focus(function() {
      jQuery(this).next().slideDown();
    });

    // Show color picker on blur
    jQuery('.color').blur(function() {
      jQuery(this).next().slideUp();
    });



  }); // end document


})(jQuery);