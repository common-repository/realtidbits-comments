(function() {
    tinymce.create('tinymce.plugins.pushquote', {
        init : function(ed, url) {
            ed.addButton('pushquote', {
                title : 'Add a Pushquote',
                image : url+'/images/pushquote.png',
                onclick : function() {
					// triggers the thickbox
					jQuery('#pushquote-form').dialog({                   
						'dialogClass'   : 'wp-dialog',           
						'modal'         : true,
						'width'			: 400,
						'height'		: 80,
						'autoOpen'      : true, 
						'closeOnEscape' : true
					});
					
					// handles the click event of the submit button
					jQuery('#pushquote-form').find('#pushquote-submit').unbind('click').click(function(){
						// defines the options and their default values
						// again, this is not the most elegant way to do this
						// but well, this gets the job done nonetheless
						var options = { 
							position: "left"
						};
						var table = jQuery('#pushquote-form').find('table');
						var shortcode = '[rtb-pushquote';
						
						for( var index in options) {
							var value = table.find('#pushquote-' + index).val();
							
							// attaches the attribute to the shortcode only if it's different from the default value
							if ( value !== options[index] )
								shortcode += ' ' + index + '="' + value + '"';
						}
						
						shortcode += ']' + ed.selection.getContent({format : 'text'}) +'[/rtb-pushquote]';
						// inserts the shortcode into the active editor
						tinyMCE.activeEditor.execCommand('mceInsertContent', 0, shortcode);
						
						// closes Thickbox
						jQuery("#pushquote-form").dialog("close");
					});
				}
            });
        },
        createControl : function(n, cm) {
            return null;
        }
    });
    tinymce.PluginManager.add('pushquote', tinymce.plugins.pushquote);
    
    // executes this when the DOM is ready
	jQuery(function(){
		// creates a form to be displayed everytime the button is clicked
		// you should achieve this using AJAX instead of direct html code like this
		var form = jQuery('<div id="pushquote-form" title="Add a pushquote" class="dp_dialogModal"><table id="pushquote-table" class="form-table">\
			<tr class="row">\
				<td><span>Position</span></td>\
				<td><select id="pushquote-position" name="position">\
				<option value="left">Left</option>\
				<option value="right">Right</option>\
				</select><br />\
			    </td>\
				<td>\
				<input type="button" id="pushquote-submit" class="button" value="Make it so" name="submit" />\
				</td>\
			</tr>\
		</table>\
		</div>');
		
		form.appendTo('body').hide();
		
		
	});
})();
