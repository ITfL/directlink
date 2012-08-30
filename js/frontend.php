<?php 
header("Content-type: text/javascript");
?>

$(document).ready(function(){
	folder_functions();
});


/**
 * helper-function to toggle between two classes of an jquery element
 * 
 * @param jq_element - the jQuery element to toggle classes of
 * @param classes_array - array of two classes
 */
function toggle_classes(jq_element, classes_array) {
	var frumpy_old_variable_name = 0;
	var fancy_new_variable_name = 1;
	if(!jq_element.hasClass(classes_array[0])) {
		frumpy_old_variable_name = 1;
		fancy_new_variable_name = 0;
	}
	jq_element.removeClass(classes_array[frumpy_old_variable_name]);
	jq_element.addClass(classes_array[fancy_new_variable_name]);
}


/**
 * This function toggles the folders in folder-view [open / close] and also
 * changes the folder items via classes
 */
function folder_functions() {
	// get all elements which sould be chlickable
	$('.file_name_text, .file_name > .dl_ressource_image, .folder_name_text, .folder_name > .dl_ressource_image').each(function(index, value){
		$(value).click(function(event) {
			var div_class = $(event.target).parent().find('[class$="_name_text"]').attr('class');
			var content_type = '';
			if(div_class) {
				content_type = (div_class.split('_'))[0];
				if(content_type == "folder") {
					// toggle the elements
					$(event.target).parent().parent().find('.content_pane:first').toggle();
					var folder_element = $(event.target).parent().find('.dl_folder:first');
					// and change css class of them to change image
					toggle_classes(folder_element, ['dl_folder_closed', 'dl_folder_opened']);
				}
			}
		});
	});
}


