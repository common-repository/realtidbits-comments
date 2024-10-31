(function() {
    tinymce.create('tinymce.plugins.forum', {
        init : function(ed, url) {
            ed.addButton('forum', {
                title : 'Add a Forum',
                image : url+'/images/forum.png',
                onclick : function() {
					// triggers the thickbox
					jQuery('#forum-form').dialog({                   
						'dialogClass'   : 'wp-dialog',           
						'modal'         : true,
						'width'			: 400,
						'height'		: 80,
						'autoOpen'      : true, 
						'closeOnEscape' : true
					});
					
					// handles the click event of the submit button
					jQuery('#forum-form').find('#forum-submit').unbind('click').click(function(){
						// defines the options and their default values
						// again, this is not the most elegant way to do this
						// but well, this gets the job done nonetheless
						var options = { 
							version: "old"
						};
						var table = jQuery('#forum-form').find('table');
						var shortcode = '[rtb-forum';
						
						for( var index in options) {
							var value = table.find('#forum-' + index).val();
							
							// attaches the attribute to the shortcode only if it's different from the default value
							if ( value !== options[index] )
								shortcode += ' ' + index + '="' + value + '"';
						}
						
						shortcode += ']';
						// inserts the shortcode into the active editor
						tinyMCE.activeEditor.execCommand('mceInsertContent', 0, shortcode);
						
						// closes Thickbox
						jQuery("#forum-form").dialog("close");
					});
				}
            });
        },
        createControl : function(n, cm) {
            return null;
        }
    });
    tinymce.PluginManager.add('forum', tinymce.plugins.forum);
    
    // executes this when the DOM is ready
	jQuery(function(){
		// creates a form to be displayed everytime the button is clicked
		// you should achieve this using AJAX instead of direct html code like this
		var form = jQuery('<div id="forum-form" title="Add a Forum" class="dp_dialogModal"><table id="forum-table" class="form-table">\
			<tr class="row">\
				<td><span>Version</span></td>\
				<td><select id="forum-version" name="version">\
				<option value="old">Old</option>\
				<option value="new">New</option>\
				</select><br />\
			    </td>\
				<td>\
				<input type="button" id="forum-submit" class="button" value="Make it so" name="submit" />\
				</td>\
			</tr>\
		</table>\
		</div>');
		
		form.appendTo('body').hide();
		
		
	});
})();
