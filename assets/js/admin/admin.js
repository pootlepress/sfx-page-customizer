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

        $('.sfxpc-field .color-picker-hex').wpColorPicker({
            //change: function() {
            //    var $pickerHex =
            //    control.setting.set( picker.val() );
            //},
            //clear: function() {
            //    control.setting.set( '' );
            //}
        });

        $('.sfxpc-field .upload-button').click(function () {

            var $textField = $(this).parent().find('input');

            window.sfxPCMetaBoxUploadField = $textField;

            window.send_to_editor = function (html) {

                if (typeof window.sfxPCMetaBoxUploadField != 'undefined' && window.sfxPCMetaBoxUploadField != null) {

                    var itemurl = '';
                    // itemurl = $(html).attr( 'href' ); // Use the URL to the main image.

                    if ( $(html).html(html).find( 'img').length > 0 ) {

                        itemurl = $(html).html(html).find( 'img').attr( 'src' ); // Use the URL to the size selected.

                    } else {

                        // It's not an image. Get the URL to the file instead.

                        var htmlBits = html.split( "'" ); // jQuery seems to strip out XHTML when assigning the string to an object. Use alternate method.

                        itemurl = htmlBits[1]; // Use the URL to the file.

                        var itemtitle = htmlBits[2];

                        itemtitle = itemtitle.replace( '>', '' );
                        itemtitle = itemtitle.replace( '</a>', '' );

                    } // End IF Statement

                    var image = /(^.*\.jpg|jpeg|png|gif|ico*)/gi;

                    if (itemurl.match(image)) {

                    } else {
                    }

                    //console.log(window.sfxPCMetaBoxUploadField);
                    //console.log(itemurl);
                    window.sfxPCMetaBoxUploadField.val(itemurl);
					$bg_options.show(0);
					
                    //console.log(window.sfxPCMetaBoxUploadField.val());

                    tb_remove();

                } else {
                    window.original_send_to_editor(html);
                }

                // Clear the formfield value so the other media library popups can work as they are meant to. - 2010-11-11.
                window.sfxPCMetaBoxFormField = '';

            };
            tb_show('', 'media-upload.php?post_id=0&amp;title=Image&amp;type=image&amp;TB_iframe=true');
            return false;
        });
    });

})(jQuery);