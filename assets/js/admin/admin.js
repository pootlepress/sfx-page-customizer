/**
 * Created by Alan on 16/2/2015.
 */
(function ($) {

    $(document).ready(function () {
		//Hiding background customizing options if .sfxpc-field.background-image .image-upload-path is blank
		$bg_url = $('.sfxpc-field.background-image .image-upload-path');
		$bg_options = $('.sfxpc-field.background-repeat, .sfxpc-field.background-position, .sfxpc-field.background-attachment');

		if($bg_url.val() == ''){
			$bg_options.hide(0);
		}

		$bg_url.change(function(){
			if($bg_url.val() == ''){
				$bg_options.hide(0);
			}else{$bg_options.show(0);}
		})

        //wpColorPicker
		$('.sfxpc-field .color-picker-hex').wpColorPicker();
		
		// Uploading Fields aka media selection
		var file_frame;
		$('.sfxpc-field .upload-button').live('click', function( event ){
			event.preventDefault();

			$textField = $(this).siblings('input');

			// If the media frame already exists, reopen it.
			if ( file_frame ) {
			  file_frame.open();
			  return;
			}

			// Create the media frame.
			file_frame = wp.media.frames.file_frame = wp.media({
			  title: $( this ).data( 'uploader_title' ),
			  button: {
				text: $( this ).data( 'uploader_button_text' ),
			  },
			  multiple: false  // Set to true to allow multiple files to be selected
			});

			// When an image is selected, run a callback.
			file_frame.on( 'select', function() {
			  // We set multiple to false so only get one image from the uploader
			  attachment = file_frame.state().get('selection').first().toJSON();

			  // Do something with attachment.id and/or attachment.url here
			  $textField.val(attachment.url)
			  $textField.change();

			});

			// Finally, open the modal
			file_frame.open();
		});

		// Layout graphical radio buttons
		//Hiding the radio buttons
		$('.sfxpc-field.layout input').css('display', 'none')
		//Highlighting the selected image
		$('.sfxpc-field.layout input:checked').siblings('img').css({border: '2px solid #999'});
		//Function for selecting radio on clicking on image
		$('.sfxpc-field.layout img').on('click', function(){
			$t = $(this);
			$('.sfxpc-field.layout img').css({border: '2px solid #ccc'});
			$t.css({border: '2px solid #999'});
			$t.siblings('input').attr('checked', 'checked').change();
		});
		
    });
    $(document).ready(function () {
		//Tabbed Navigation
		$('.sfxpc-tabs-wrapper').tabs();
	});

})(jQuery);