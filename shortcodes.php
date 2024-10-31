<?php
// Shortcode

function pushquote_simple_shortcode($atts, $content) {
	extract(shortcode_atts(array(
		'position' => 'left'
	), $atts));
	
	return $content."<span style='display:none;' class='realtidbitsApp rtb-pq-".$position."'>".$content."</span>";
}

function pushquote_process_shortcode($content) {
    global $shortcode_tags;
    // Backup current registered shortcodes and clear them all out
    $orig_shortcode_tags = $shortcode_tags;
    $shortcode_tags = array();
		add_shortcode('rtb-pushquote', 'pushquote_simple_shortcode');

    // Do the shortcode (only the one above is registered)
    $content = do_shortcode($content);
    // Put the original shortcodes back
    $shortcode_tags = $orig_shortcode_tags;
    return $content;
}
add_filter('the_content', 'pushquote_process_shortcode', 7);


function audiorecorder_simple_shortcode($atts, $content) {
	global $post;
	
	extract(shortcode_atts(array(
		'title' => $post->post_name
	), $atts));
	
	//function rtb_forum() {
		return '
			<div id="rtb_audiorecorder"></div>
			<div class="rtb_audiorecorder_wrap">
				
				<!--Time: <span id="time">00:00</span>
				<br>-->
				<span id="status"></span>
				<div id="levelbase">
				  
				  <div id="levelbar"></div>
				  
				</div>
				<input type="button" value="Record" onclick="$(\'#flashrecarea\').css(\'visibility\', \'visible\');$.jRecorder.record(-1); jQuery(this).hide(); jQuery(\'.audiorecorder-stop\').show(); jQuery(\'.audiorecorder-send\').hide();" class="audiorecorder-record button">  
				<input type="button" value="Stop" onclick="$.jRecorder.stop(); jQuery(this).hide(); jQuery(\'.audiorecorder-record\').show(); jQuery(\'.audiorecorder-send\').show();" style="display:none;" class="audiorecorder-stop button">  
				<input type="button" value="Send" onclick="$.jRecorder.sendData(); jQuery(this).hide();" style="display:none;" class="audiorecorder-send button">
			</div>
			<script type="text/javascript" src="'.rtb_plugin_url( 'js/jRecorder.js' ).'"></script> 
			<script type="text/javascript"> 
				jQuery.jRecorder( {
			
				   "swf_path": "'.rtb_plugin_url("js/jRecorder_1.2.swf").'",
				   callback_started_recording:     function(){callback_started(); },
					callback_stopped_recording:     function(){callback_stopped(); },
					callback_activityTime:     function(time){callback_activityTime(time); },
					callback_activityLevel:          function(level){callback_activityLevel(level); },
					callback_finished_sending:     function(time){ callback_finished_sending() },
					callback_sent:     function(time){ callback_sent() },
				   "host": "'.rtb_plugin_url("audiorecorder/acceptfile.php?filename=".get_the_ID()).'"
				}, jQuery("#rtb_audiorecorder"));
				
				function callback_activityLevel(level)
                  {
                    
                    $("#level").html(level);
                    
                    if(level == -1)
                    {
                      $("#levelbar").css("width",  "2px");
                    }
                    else
                    {
                      $("#levelbar").css("width", (level * 2)+ "px");
                    }
                    
                    
                  }
				  
				function callback_finished()
                  {
      
                      $("#status").html("Finished");
                    
                  }
                  
                  function callback_started()
                  {
      
                      $("#status").html("Recording...");
                    
                  }
                  
                  function callback_error(code)
                  {
                      $("#status").html("Error, code:" + code);
                  }
                  
                  
                  function callback_stopped()
                  {
                      $("#status").html("Stopped");
                  }

                  function callback_finished_recording()
                  {
                    
                      $("#status").html("Finished");
                    
                    
                  }
                  
                  function callback_finished_sending()
                  {
                    
                      $("#status").html("Sending...");
                      
                      
                  }
				  
				  function callback_sent()
                  {
                    
                      $("#status").html("Sent! Your contribution has been successful but awaits moderator approval");
                    
                    
                  }
                  
                  function callback_activityTime(time)
                  {
                   
                    $("#time").html(time);
                    
                  }
			</script>';
	//}
	//add_action('wp_footer', 'rtb_forum');
}
add_shortcode('rtb-audiorecorder', 'audiorecorder_simple_shortcode');

function forum_simple_shortcode($atts, $content) {
	global $post;
	$return = "";
	extract(shortcode_atts(array(
		'title' => $post->post_name,
		'version' => ''
	), $atts));
	$unique = time().rand();
	//function rtb_forum() {
		if($version == "new") {
		$return = '
		<div id="rtb-forum'.$unique.'"></div>
		<script type="text/javascript">
		var rtbForum = {};
		Echo.Loader.initApplication({
			"script": "http://cdn.realtidbits.com/libs/v3/forums/forums.min.js",
			"component": "RTB.Apps.Forums",
			"backplane": {
				"busName": "'.get_option('rtb_comments_backplane_busname').'",
				"serverBaseURL": "https://api.echoenabled.com/v1"
			},
			"config": {
				"target": document.getElementById("rtb-forum'.$unique.'"),
				"appkey": "'.strtolower(get_option('rtb_comments_appkey')).'",
				"janrain": { "appUrl": "'.strtolower(get_option('rtb_comments_rpx')).'" },
				"settings": {';
					if(get_option('rtb_comments_login') == 'wordpress') {
						$return .= '
						"auth": {
						  "loginButton": false
						},';
					}
				$return .= '
					"topics": [] // array of topics (optional)
				},
				"cdnBaseURL": {
					"sdk": "",
					"RTB": "http://master.realtidbits.com/libs/v3"
				},
				"cssURL": "http://master.realtidbits.com/libs/v3/forums/style.css",
				"targetURL": "'.$title.'",
				"permalink": "http://voiceofsandiego.org/the-plaza/", // homepage for this forum
				"ready": function() {
					try {
					forumIdeaMeltNotifiers(this);
					} catch(err) {};
					
					rtbForum = this;
					
					// subscribe to global analytics event
		            Echo.Events.subscribe({"topic": "rtbAnalytics", "handler": function(topic, eventData) {
						// do something here
						var name = eventData.event; // event name, ex: "post", "like" etc.
						
						<!-- Start of Woopra Code -->
						woopraTracker.pushEvent({
							name: name,
							type: "forum",
							url: document.location.href
						});
					
						<!-- End of Woopra Code -->
					}});
				}
			}
		});
		</script>
		';
		} else {
			$return = '<script type="text/javascript" src="http://cdn.realtidbits.com/libs/v1/forum/forum.js"></script> 
			<script type="text/javascript"> 
			jQuery(document).ready(function() {
				new RealTidbits.Forum({ 
					"busName": "'.get_option('rtb_comments_backplane_busname').'", 
					"appkey": "'.strtolower(get_option('rtb_comments_appkey')).'", 
					"forumID": "'.$title.'",
					"rpx": "'.strtolower(get_option('rtb_comments_rpx')).'",
					"settings": {';
					if(get_option('rtb_comments_login') == 'wordpress') {
					$return .= '
						"auth": {
						  "loginButton": false
						}';
					}
				$return .= '
					}
				}); 
			});
			</script>';
		}
		
		return $return;
	//}
	//add_action('wp_footer', 'rtb_forum');
}
add_shortcode('rtb-forum', 'forum_simple_shortcode');

function pinboard_simple_shortcode($atts, $content) {
	global $post;
	
	extract(shortcode_atts(array(
		'title' => $post->post_name
	), $atts));
	
	//function rtb_forum() {
		return '<div id="rtb-mypinboard"></div>
			<script type="text/javascript" src="http://realtidbits.com/libs/v1/pinboard/pinboard.js"></script>
			<script type="text/javascript">
				var target_url = "'.(substr(rtb_cur_page(), -1) == '/' ? substr(rtb_cur_page(),0 , -1) : rtb_cur_page() ).'"; /*no trailing slash, please*/
				new RealTidbits.Pinboard({
					"appkey": "'.strtolower(get_option('rtb_comments_appkey')).'", 
					"backplane": {
					"busName": "'.get_option('rtb_comments_backplane_busname').'", 
					"rpx": "'.strtolower(get_option('rtb_comments_rpx')).'"
				},
				"socialSharing": {
					"appId" : "'.strtolower(get_option('rtb_comments_rpx')).'",
					"xdReceiver": "/rpx_xdcomm.html"
				},
				"target": document.getElementById("rtb-mypinboard"),
				"streamFilters": [
					{"label": "All", "target": target_url, "query": "childrenof:" + target_url + ""},/*
					{"label": "Official", "target": target_url, "query": "childrenof:" + target_url + " markers:official"},
					{"label": "Fans", "target": target_url, "query": "childrenof:" + target_url + " markers:fans"},*/
					{"label": "Pins", "target": target_url, "query": "childrenof:" + target_url + " markers:pins"}
				],
				"addContentButton": true,
				"toolbarBookmarklet": true,
				"pinitMarkers": "pins",
				"selectedContentFilter": 2, 
				"cssDefault": "http://realtidbits.com/libs/v1/pinboard/pinboard.css"
				});
			</script>';
	//}
	//add_action('wp_footer', 'rtb_forum');
}
add_shortcode('rtb-pinboard', 'pinboard_simple_shortcode');

function gallery_simple_shortcode($atts, $content) {
	global $post;
	
	extract(shortcode_atts(array(
		'title' => $post->post_name
	), $atts));
	
	//function rtb_forum() {
		return '<div id="rtb-myGallery"></div>
			<script type="text/javascript" src="http://cdn.echoenabled.com/sdk/v3/loader.js"></script>
			<script type="text/javascript">
				Echo.Loader.initApplication({
					"script": "http://realtidbits.com/libs/v3/gallery/core.js",
					"component": "RTB.Apps.Gallery",
					"backplane": {
						"busName": "'.get_option('rtb_comments_backplane_busname').'", 
						"serverBaseURL": "https://api.echoenabled.com/v1"
					},
					"config": {
						"cdnBaseURL": {
							"RTB": (window.location.protocol === "https:" ? "https://ssl." : "http://") + "realtidbits.com/libs/v3"
						},
						"target": document.getElementById("rtb-myGallery"),
						"appkey": "'.strtolower(get_option('rtb_comments_appkey')).'", 
						"targetURL": window.location.protocol + "//" + document.domain + "/'.get_the_ID().'",
						"janrain": {
							"app": "realtidbitsdev",
							"appUrl": "'.strtolower(get_option('rtb_comments_rpx')).'"
						},
						"settings": {
							"gallery": {
								"itemWidth": "100px",
								"itemHeight": "100px",
								"uniqueImages": false
							},
							"modal": {
								"comments": true,
								"itemUniqueID": false,
								"commentSettings": {
									"stream": {
										"childrenItemsPerPage" : 1,
										"childrenSortOrder": "reverseChronological",
										"bozoFilter": false, // always shows user s comments to user (even if user is banned)
										"itemsPerPage": 5,
										"preModeration": false, // comments must be approved to show in stream
										"replyLevels": 2, // number of reply levels
										"streamQueryURLs": [
											"'.(substr(rtb_cur_page(), -1) == '/' ? substr(rtb_cur_page(),0 , -1) : rtb_cur_page() ).'",
											window.location.protocol + "//" + document.domain + "/'.get_the_ID().'"
										]
									},
									"tokbox": {"enabled": true},
									"topComments": {
										"enabled": true
									}
								}
							}
						},
						"cssURL": "http://realtidbits.com/libs/v3/gallery/gallery.css",
						"ready": function() {
							var app = this;
							app.events.subscribe({"topic": "RTB.Apps.Gallery.onItemRender", "handler": function(topic, data) {
								// var element = data.element;
								// var item = data.item;
							}});
							app.events.subscribe({"topic": "RTB.Apps.Gallery.onModalShow", "handler": function(topic, data) {
								// var element = data.element;
								// var item = data.item;
							}});
						}
					},
				});
			</script>';
	//}
	//add_action('wp_footer', 'rtb_forum');
}
add_shortcode('rtb-gallery', 'gallery_simple_shortcode');

function liveblog_simple_shortcode($atts, $content) {
	global $post;
	
	extract(shortcode_atts(array(
		'title' => $post->post_name
	), $atts));
	
	//function rtb_liveblog() {
		return '<script type="text/javascript" src="http://cdn.realtidbits.com/libs/v1/comments/comments.js"></script>
		<script type="text/javascript">
		jQuery(document).ready(function() {
			new RealTidbits.Comments({
			"appkey": "'.strtolower(get_option('rtb_comments_appkey')).'",
			"backplane": {
				"busName": "'.get_option('rtb_comments_backplane_busname').'", 
				"rpx": "'.strtolower(get_option('rtb_comments_rpx')).'"
			},
			"socialSharing": { 
				"appUrl": "'.strtolower(get_option('rtb_comments_rpx')).'"
			},
			"readOnly": true,
			"hideLogin": true,
			"rssFeed": false,
			"emailSubscribe": false
			});
		});
		</script>
		<noscript>Please enable JavaScript to view the <a class=" external" rel="external nofollow" href="http://realtidbits.com/?ref_noscript" title="http://realtidbits.com/?ref_noscript" target="_blank">Comments powered by RealTidbits.</a></noscript>';
	//}
	//add_action('wp_footer', 'rtb_forum');
}
add_shortcode('rtb-liveblog', 'liveblog_simple_shortcode');

?>