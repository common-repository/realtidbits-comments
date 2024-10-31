(function() {
    tinymce.create('tinymce.plugins.pinboard', {
        init : function(ed, url) {
            ed.addButton('pinboard', {
                title : 'Add a Pinboard',
                image : url+'/images/pinboard.png',
                onclick : function() {
					// triggers the thickbox
					/*jQuery('#pinboard-form').dialog({                   
						'dialogClass'   : 'wp-dialog',           
						'modal'         : true,
						'width'			: 400,
						'height'		: 80,
						'autoOpen'      : true, 
						'closeOnEscape' : true
					});*/
					
					// handles the click event of the submit button
					//jQuery('#pinboard-form').find('#pinboard-submit').unbind('click').click(function(){
						// defines the options and their default values
						// again, this is not the most elegant way to do this
						// but well, this gets the job done nonetheless
						var options = { 
						};
						var table = jQuery('#pinboard-form').find('table');
						var shortcode = '[rtb-pinboard';
						
						for( var index in options) {
							var value = table.find('#pinboard-' + index).val();
							
							// attaches the attribute to the shortcode only if it's different from the default value
							if ( value !== options[index] )
								shortcode += ' ' + index + '="' + value + '"';
						}
						
						shortcode += ']';
						// inserts the shortcode into the active editor
						tinyMCE.activeEditor.execCommand('mceInsertContent', 0, shortcode);
						
						// closes Thickbox
					//	jQuery("#pinboard-form").dialog("close");
					//});
				}
            });
        },
        createControl : function(n, cm) {
            return null;
        }
    });
    tinymce.PluginManager.add('pinboard', tinymce.plugins.pinboard);
    
    // executes this when the DOM is ready
	jQuery(function(){
		// creates a form to be displayed everytime the button is clicked
		// you should achieve this using AJAX instead of direct html code like this
		var form = jQuery('<div id="pinboard-form" title="Add a Pinboard" class="dp_dialogModal"><table id="pinboard-table" class="form-table">\
			<tr class="row">\
			</tr>\
		</table>\
		</div>');
		
		form.appendTo('body').hide();
		
		
	});
})();
