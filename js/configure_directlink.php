<?php 

require_once(dirname(dirname(dirname(__FILE__))).'/../config.php');
require_once(dirname(__FILE__).'/../lib.php');
require_once(dirname(__FILE__).'/../locallib.php');


header("Content-type: text/javascript");


?>

/**
 * @package mod
 * @subpackage directlink
 * @copyright 2012 onwards Michael Hamatschek and Hans-Christian Sperker
 *            {@link http://www.uni-bamberg.de/itfl-service}
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * initialize the site
 */
 
changes_in_connection_manager = false;
element_visibility = false;
 
$(document).ready(function(){
	disable_content();
	$('#id_existing_connections').msDropDown();
	ffc_event();
	patch_msDropDown();
	oTable = $('#myTable').dataTable({
		"aaSorting": [[ 2, "desc" ]],
		"aoColumnDefs": [{ "bSortable": false, "aTargets": [ 1, 6 ] }],
		"bProcessing": true,
		"sAjaxSource": "../mod/directlink/manage_connections.php?course=" + course_id
	});
	
	$( "#tabs" ).tabs();
	
	
	$('#connection_properties .fcontainer').appendTo('#new-connection');
	$('#connection_properties').hide();
	
	if($('#id_share_user_pwdunmaskdiv').find('label').size()) {
		$('#id_share_user_pwdunmaskdiv').find('label').each(function(index, element){if(index == 1){$(element).remove();}});
	}
	if($('#id_share_user_pwdunmaskdiv').find('input').size()) {
		$('#id_share_user_pwdunmaskdiv').find('input').each(function(index, element){if(index == 1){$(element).remove();}});
	}
	
	$("#myTable tbody").click(function(event) {
		$(oTable.fnSettings().aoData).each(function (){
			$(this.nTr).removeClass('row_selected');
		});
		$(event.target.parentNode).addClass('row_selected');
		var data = oTable.fnGetData( event.target.parentNode );
		var id = data[0];
		
		var was_open = oTable.fnIsOpen( event.target.parentNode );
		
		$("#myTable tbody tr").each(function(){
			oTable.fnClose( this );
		});
		
		if(!was_open){
			new_row = oTable.fnOpen( event.target.parentNode , "", "connection_info" );
			data = {course: course_id, cid: id};
			cached_request.ajax("../mod/directlink/connection_info.php", data, function(data){
					$('.connection_info').append(data);
				});
		}
	});
	
	
	
	oTable.fnSetColumnVis( 0, false );
});

/**
 * update datatable to reload ajax
 * http://datatables.net/plug-ins/api
 */
$.fn.dataTableExt.oApi.fnReloadAjax = function ( oSettings, sNewSource, fnCallback, bStandingRedraw )
{
    if ( typeof sNewSource != 'undefined' && sNewSource != null )
    {
        oSettings.sAjaxSource = sNewSource;
    }
    this.oApi._fnProcessingDisplay( oSettings, true );
    var that = this;
    var iStart = oSettings._iDisplayStart;
    var aData = [];
 
    this.oApi._fnServerParams( oSettings, aData );
     
    oSettings.fnServerData( oSettings.sAjaxSource, aData, function(json) {
        /* Clear the old information from the table */
        that.oApi._fnClearTable( oSettings );
         
        /* Got the data - add it to the table */
        var aData =  (oSettings.sAjaxDataProp !== "") ?
            that.oApi._fnGetObjectDataFn( oSettings.sAjaxDataProp )( json ) : json;
         
        for ( var i=0 ; i<aData.length ; i++ )
        {
            that.oApi._fnAddData( oSettings, aData[i] );
        }
         
        oSettings.aiDisplay = oSettings.aiDisplayMaster.slice();
        that.fnDraw();
         
        if ( typeof bStandingRedraw != 'undefined' && bStandingRedraw === true )
        {
            oSettings._iDisplayStart = iStart;
            that.fnDraw( false );
        }
         
        that.oApi._fnProcessingDisplay( oSettings, false );
         
        /* Callback user function - for event handlers etc */
        if ( typeof fnCallback == 'function' && fnCallback != null )
        {
            fnCallback( oSettings );
        }
    }, oSettings );
};

function send_changes() {
 	var cid = $('#edit_connection_id').val();
	var name = $('#edit_connection_name').val();
	var server = $('#edit_connection_server').val();
	var domain = $('#edit_connection_domain').val();
	var share = $('#edit_connection_share').val();
	var user = $('#edit_connection_user').val();
	var pwd = $('#edit_connection_password').val();
	var type = $("input[name='edit_connection_share_access_type']:checked").val();
	
	var foo = {
		course: course_id,
		cid: cid,
		name: name,
		server: server,
		domain: domain,
		share: share,
		user: user,
		pwd: pwd,
		type: type
	};
	
	$.ajax({
		url: '../mod/directlink/change_connection.php',
		data: foo,
		dataType: 'json',
		success: function(data){
			oTable.fnReloadAjax();
			if(data.valid){
				message('success', '<?php echo get_string('manage_changes_success', 'directlink'); ?>');
				discard_edit();
				changes_in_connection_manager = true;
			}
			else {
				message('problem', '<?php echo get_string('manage_changes_problem', 'directlink'); ?>');
			}
		}
	}); 
}

/**
 * function to delete a connection
 */
function delete_connection(connection_id) {
	
	$.ajax({
		url: '../mod/directlink/delete_connection.php',
		data: {
			course: course_id,
			cid: connection_id
		}
	});	
	cached_request.__flush_cache();
	oTable.fnReloadAjax();
	changes_in_connection_manager = true;
}

function edit_connection(connection_id) {
disable_fields_edit_connection();
	$('#edit_connection').slideDown();
	$.ajax({
		url: '../mod/directlink/connection_info.php',
		data: {
			course: course_id,
			cid: connection_id,
			json: 1
		},
		dataType: 'json',
		success: fill_connection_data
	});
}

/**
 * fill the connection form for editing
 * 
 * @param data json data containing the connections credentials
 */
function fill_connection_data(data){
	$('#edit_connection_id').val(data.id);
	$('#edit_connection_name').val(data.name);
	$('#edit_connection_server').val(data.server);
	$('#edit_connection_domain').val(data.domain);
	$('#edit_connection_share').val(data.share);
	$('#edit_connection_user').val(data.user);
	$("input[name='edit_connection_share_access_type'][value='"+data.type+"']").attr("checked","checked");
}

function discard_edit() {
	$('#edit_connection').slideUp();
	$('#connection_name').val('');
	$('#edit_connection_id').val('');
	$('#edit_connection_name').val('');
	$('#edit_connection_server').val('');
	$('#edit_connection_domain').val('');
	$('#edit_connection_share').val('');
	$('#edit_connection_user').val('');
	$('#edit_connection_password').val('');
	$("input[name='connection_share_access_type'][value='private']").attr("checked","checked");
}
/**
 * Simple cached ajax-request for connection infos
 */
cached_request = {
	__cache : {},
	__TTL_in_sec: 900, // 900 = 15min
	__get_time_in_sec: function() {
		return Math.round((new Date()).getTime() / 1000);
	},
	__write : function(hash, data){
		this.__cache[hash] = new Array(this.__get_time_in_sec(), data);
	},
	__read : function(hash){
		if((this.__cache[hash] != undefined) && ((this.__get_time_in_sec() - this.__cache[hash][0]) < this.__TTL_in_sec)) {
			return this.__cache[hash][1];
		}
		else {
			return null;
		}
	},
	__flush_cache: function() {
		this.__cache = {};
	},
	// A hash-function which returns a number hash representing a string
	__get_hash: function(string){
	    var hash = 0;
	    if (string.length == 0) return hash;
	    for (var i = 0; i < string.length; i++) {
	        char = string.charCodeAt(i);
	        hash = ((hash<<5)-hash)+char;
	        hash = hash & hash; // Convert to 32bit integer
	    }
	    if(hash < 0) {
	    	hash = '1' + Math.abs(hash);
	    }
	    else{
	    	hash = '0' + hash;
	    }
	    return hash + '';
	},
	// this function stores data returned from an ajax call and calls the callback
	__store_n_call: function(hash, data, callback){
		this.__write(hash, data);
		callback(data);
	},
	// This function represents the ajax call...
	ajax : function (url, data, callback) {
		// check that jquery is enabled
		if(typeof jQuery != "undefined"){
			var hash = this.__get_hash(url+JSON.stringify(data));
			// if read is not null we have some data for this request in cache...
			if((chached_data = this.__read(hash)) != null) {
				callback(chached_data);
			}
			// else just start a new request
			else {
				(function(url, callback, hash){
					$.ajax({
						url: url,
						type: "POST",
						data: data, 
						success: function(data){
							cached_request.__store_n_call(hash, data, callback);
						}
					});
				})(url, callback, hash);
			}
		}
	}
};


/**
 * Set change event for radio buttons to unselect content
 * if changed
 */
function ffc_event() {
	$('input[name="ffc"]').change(function(){
		unselect_content();
	});
}

function unselect_content() {
	$('#id_path_to_file').attr('value', '');
	$('.folder_name').removeClass('resource_selected sub_resource_selected');
	$('.file_name').removeClass('resource_selected sub_resource_selected');
	$('.dl_resource_image').removeClass('resource_selected sub_resource_selected');
}

/**
 * patches msDropDown such that it asks before changing the connection
 */
function patch_msDropDown() {
	$('#id_existing_connections').ready(function(){
		$('#id_existing_connections').data('dd').__open = $('#id_existing_connections').data('dd').open;
		
		$('#id_existing_connections').data('dd').open = function(){
			if(form_filled()){
				if(confirm('<?php echo get_string('js_confirm', 'directlink'); ?>')) {
					send_discard_credentials();
				}
				else {
					return false;
				}
			}
			$('#id_existing_connections').data('dd').__open();
		};
	});
}

/**
 * check if the form has been filled
 * 
 * @returns true if user entered some stuff
 */
function form_filled() {
	if($('#id_connection_name').val() != '' || $('#id_user_share').val() != '') {
		return true;
	}
	return false;
}

/**
 * Discards the previously sended credentials. Clears all fields to start again.
 * 
 * @returns false - to prevent the button from sending the form
 */
function send_discard_credentials() {
	
	$.ajax({
		url:'../mod/directlink/umount.php',
		data: {
			id: $('input[name="course"]').val()
		},
		dataType: 'json'
	});	
	
	clear_all_fields();
	$('[class^="notify"]').remove();
	$('#id_existing_connections').data('dd').selectedIndex(0);
	return false;
}

/**
 * clear all fields of the form. Also enables them again 
 */
function clear_all_fields() {
	resource = '';
	$('#id_connection_name').removeAttr('disabled');
	$('#id_server').removeAttr('disabled');
	$('#id_domain').removeAttr('disabled');
	$('#id_user_share').removeAttr('disabled');
	$('#id_share_user').removeAttr('disabled');
	$('#id_share_user_pwd').removeAttr('disabled');
	$('#id_share_user_pwdunmask').removeAttr('disabled');
	$('input:[name=share_access_type]').removeAttr('disabled');
	
	$('#id_connection_name').val('');
	$('#id_server').val('');
	$('#id_domain').val('');
	$('#id_user_share').val('');
	$('#id_share_user').val('');
	$('#id_share_user_pwd').val('');
	$('#id_share_user_pwdunmask').val('');
	$('input[name=path_to_file]').val('');
	$('input[name=reference]').val('');
	$('#id_intro').val('');
	
	$('#send_credentials').removeAttr('disabled');
	$('#discard_credentials').attr('disabled', 'disabled');
	
	
	disable_content();
}

function tab_toggler(show) {
	
	if(show && element_visibility) {
		$('#modstandardelshdr').show();
		$('#linktype').show();
		$('#general').show();
		$('#choosefile').show();
	}
	else {
		if($('#modstandardelshdr').is(':visible')) {
			element_visibility = true;
			
			$('#modstandardelshdr').hide();
			$('#linktype').hide();
			$('#general').hide();
			$('#choosefile').hide();
		}
	}
}

/**
 * Disable content not used right at the beginning
 */
function disable_content(){
	$('#modstandardelshdr').hide();
	$('#linktype').hide();
	$('#general').hide();
	$('#choosefile').hide();
	$('input[name*="submit"]').each(function(index){
		$(this).attr('disabled','disabled');
	});
	
	$('#id_path_to_file').parent().parent().css('visibility','hidden');
};

/**
 * Show a moodle style message
 * 
 * @param type - the type of message [ success | problem ]
 * @param message - the message 
 */
function message(type, message) {
	// remove any previously shown message
	$('[class^="notify"]').remove();
	// display the new message
	$('#maincontent').after('<div class="notify'+type+'">'+message+'</div>');
}

/**
 * selects a resource in the file picker
 * 
 * @param resource
 */
function mark_selected_resource(resource) {
	var element = document.getElementById(resource);
	$(element).click();

	
	
//	var ffc = $('input[name=ffc][checked=checked]').val();
//	var tmp_resource = $(document.getElementById(resource)).parent();
//	
//	if(ffc == 'content') {
//		tmp_resource.parent().find('.file_name:not(:first), .folder_name:not(:first)').addClass('resource_selected');
//	}
//	else if(ffc == 'folder') {
//		tmp_resource.parent().find('.folder_name:first').addClass('resource_selected');
//		tmp_resource.parent().find('.file_name, .folder_name:not(:first)').addClass('sub_resource_selected');
//	}
//	else if(ffc == 'file') {
//		tmp_resource.addClass('resource_selected');
//	}
}

/**
 * creates html code for files
 * 
 * @param filename
 * @param path
 * @returns html code
 */
function create_file_html(filename, path) {
	var fileextension = filename.split('.').reverse()[0].toLocaleLowerCase();
	return '<div class=\'file_name\'><img src="../mod/directlink/get_ressource_icon.php?extension='+ fileextension +'" class="activityicon dl_resource_image" alt="File"><span id=\''+path + filename +'\'  class="file_name_text">' + filename + '</span></div>';
}

/**
 * Creates html code for folder_pane
 * 
 * @param folder_section
 * @param file_section
 * @returns {String}
 */
function create_folder_pane_html(folder_section,  file_section, path, foldername) {
	// this is needed if a directlink is changed. The previously selected file should be selected again 
	var display = 'none';
	if(typeof resource != 'undefined') {
		if(resource.match(path)) {
			display = 'block';
		}
	}
	
	var html_code =
		'<div class=\'folder_pane\'>' +
			'<div class=\'folder_name\'>' +
				'<div class="activityicon dl_resource_image dl_folder dl_folder_closed" alt="Folder" style="margin:4px 5px 0px 0px;"></div><span id=\''+path + '\' class="folder_name_text">' + foldername +
			'</span></div>' +
			'<div class=\'content_pane\' style=\'display: '+display+';\'>' +
				'<div class=\'folders_pane\'>' +
				folder_section +
				'</div>' +
				'<div class=\'files_pane\'>' +
				file_section +
				'</div>' +
			'</div>' +
		'</div>';
	
	return html_code;
}

/**
 * Generates the HTML-Code of file-picker
 * 
 * @param foldername
 * @param folder
 * @param path
 * @returns html code
 */
function get_html_folder_statement(foldername, folder, path){
	var file_section = '';
	var folder_section = '';
	path = path + foldername + "/";

	// creates html of every file
	$.each(folder['file'], function(index, value) {
		file_section += create_file_html(index, path);
	});

	// recursive call to create html of folder content
	$.each(folder['folder'], function(index, value) { 
		folder_section += get_html_folder_statement(index, value, path);
	});
	
	var html_code = create_folder_pane_html(folder_section, file_section, path, foldername);

	return html_code;
};

/**
 * deselect user selected text in html
 */
function deselect_text(){
	if (window.getSelection) {
		  if (window.getSelection().empty) {  // Chrome
		    window.getSelection().empty();
		  } else if (window.getSelection().removeAllRanges) {  // Firefox
		    window.getSelection().removeAllRanges();
		  }
		} else if (document.selection) {  // IE?
		  document.selection.empty();
		}
}


/**
 * toggle and select events
 */
function create_file_picker_events(){
	$('.file_name_text, .file_name > .dl_resource_image, .folder_name_text, .folder_name > .dl_resource_image').each(function(index, value){
		$(value).click(function(event) {
			ffc = $('[name=ffc]:checked').val();
			content_type = ($(event.target).parent().find('[class$="_name_text"]').attr('class').split('_'))[0];
			unselect_content();
			e_parent = $(event.target).parent();
			
			if(content_type == "file" && ffc == "file") {
				$('#id_path_to_file').attr('value', e_parent.find('[class$="_name_text"]')[0].id);
				e_parent.addClass('resource_selected');
			}
			else if(content_type == "folder") {
				content_pane_display = e_parent.parent().find('.content_pane:first').css('display');
				if(ffc == "file" || content_pane_display == "none") {
					e_parent.parent().find('.content_pane:first').toggle();
					var folder_element = e_parent.find('.dl_folder:first');
					toggle_classes(folder_element, ['dl_folder_closed', 'dl_folder_opened']);
				}
				if(ffc == "folder") {
					$('#id_path_to_file').attr('value', e_parent.find('[class$="_name_text"]')[0].id);
					e_parent.parent().find('.file_name, .folder_name').addClass('sub_resource_selected');
					e_parent.removeClass('sub_resource_selected');
					e_parent.addClass('resource_selected');
				}
				else if(ffc == "content") {
					$('#id_path_to_file').attr('value', e_parent.find('[class$="_name_text"]')[0].id);
					e_parent.parent().find('.file_name, .folder_name').addClass('resource_selected');
					e_parent.removeClass('resource_selected');
				}
			}
			deselect_text();
		});
	});
}

/**
 * Inserts html code of folder_pane and binds events
 * 
 * @param json - the file object in json
 */
function show_files(json) {
	root  = '';
	
	$.each(json, function(index, value) {
		if(root == '') {
			root = index;
		}
		$('#show_files').html(get_html_folder_statement(index, value, ''));
	});

	create_file_picker_events();
	
	if(typeof resource != 'undefined' && resource != '') {
		mark_selected_resource(resource);	
	}
}


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
 * disable form fields for editing connection
 */
function disable_fields_edit_connection() {
	$('#edit_connection_server').attr('disabled', 'disabled');
	$('#edit_connection_domain').attr('disabled', 'disabled');
	$('#edit_connection_share').attr('disabled', 'disabled');
	$('#edit_connection_user').attr('disabled', 'disabled');
	$('input:[name=share_access_type]').attr('disabled', 'disabled');
}

/**
 * disable form fields
 */
function disable_fields() {
	$('#send_credentials').attr('disabled', 'disabled');
	$('#id_server').attr('disabled', 'disabled');
	$('#id_domain').attr('disabled', 'disabled');
	$('#id_user_share').attr('disabled', 'disabled');
	$('#id_share_user').attr('disabled', 'disabled');
	$('#id_share_user_pwd').attr('disabled', 'disabled');
	$('#id_share_user_pwdunmask').attr('disabled', 'disabled');
}

/**
 * enable fields
 */
function enable_fields() {
	$('#id_connection_name').removeAttr('disabled');
	$('#id_server').removeAttr('disabled');
	$('#id_domain').removeAttr('disabled');
	$('#id_user_share').removeAttr('disabled');
	$('#id_share_user').removeAttr('disabled');
	$('#id_share_user_pwd').removeAttr('disabled');
	$('#id_share_user_pwdunmask').removeAttr('disabled');
	$('input:[name=share_access_type]').removeAttr('disabled');
}

/**
 * Gets the json from server and triggers the listing of dir
 * 
 * @param json
 */
function credentials_checked(json) {
	
	if(json['valid']) {
		message('success', json['msg']);
		// show files and folder via ajax here
		$.ajax({
			url:'../mod/directlink/list_dir.php',
			data: {
				id: form_values['course']
			},
			dataType: 'json',
			context: {resource: this.resource},
			success: show_files
		});	

		$('#modstandardelshdr').show();
		$('#linktype').show();
		$('#choosefile').show();
		$('#general').show();
		$('#discard_credentials').removeAttr('disabled');
		
		disable_fields();
		
		$('input[name*=\'submit\']').each(
				function(index){
					$(this).removeAttr('disabled');
				}
		);
	}
	else {
		message('problem', json['msg']);
	}

	if(json['debug']) {
		message('problem', json['debug']);
	}
};

/**
 * check if required fields are filled
 * 
 * @returns {Boolean}
 */
function check_required_fields() {
	return form_values['connection_name'] == '' ||
	form_values['server'] == '' ||
	form_values['user_share'] == '' ||
	form_values['domain'] == '' ||
	form_values['share_user'] == '' ||
	form_values['share_user_pwd'] == '';
}

/**
 * get form values
 * 
 * @returns form object
 */
function get_form_vals() {
	form_values = {};
	$('form :input').each(function() {
		form_values[this.name] = $(this).val();
	});
	return form_values;
}

/**
 * if the submit button is clicked, fields must be enabled such that they can be
 * send to server. but only if the form is valid.
 */
function patch_submit_event() {
	$('input[name*=\'submit\']').each(
			function(index){
				$(this).click(function(){
					
//					console.info(get_form_vals());
//					
//					if(!confirm('Send form?')) {
//						return false;
//					}
					
					if(validate_mod_directlink_mod_form($('form[id="mform1"]')[0])) {
						enable_fields();
						return true;
					}
					else {
						return false;
					}
				});
			}
	);
}

/**
 * Gets called if send button is clicked
 * 
 * @param resource
 * @returns false - to not trigger from submit
 */
function send_credentials_clicked(resource) {
	form_values = get_form_vals();
	
	if(check_required_fields()){
		alert('Please complete required fileds!');
		return false;
	}
	
	if(resource == null) {
		resource = '';
	}
		
	patch_submit_event();

	$.ajax({
		url: '../mod/directlink/check_credentials.php',
		data: {
			server: form_values['server'],
			user_share: form_values['user_share'],
			domain: form_values['domain'],
			share_user: form_values['share_user'],
			share_user_pwd: form_values['share_user_pwd'],
			id: form_values['course'],
			reference: form_values['reference']
		},
		dataType: 'json',
		context: {resource: resource},
		success: credentials_checked
	});	

	return false;
};

