<!-- RealTidbits.Comments -->
<div id="rtb-latest-comments">Loading comments...</div>

<script type="text/javascript">
Backplane.init({
	"serverBaseURL" : "http://api.echoenabled.com/v1",
	"busName": "<?php echo get_option('rtb_comments_backplane_busname'); ?>"
});
</script>
<script type="text/javascript">
new Echo.Stream({
    "target": document.getElementById('rtb-latest-comments'),
    "appkey": "<?php echo get_option('rtb_comments_appkey'); ?>",
    "query": "childrenof:" + document.location.protocol + "//" + document.location.hostname + "/* sortOrder:reverseChronological children",
    "reTag": true,
    "itemsPerPage": 10,
    "viaLabel": {
          "icon": true,
          "text": true
    },
    "streamStateLabel": {
        "icon": false,
        "text": false
    },
    "contentTransformations": {
        "text": ["smileys", "hashtags", "urls", "newlines"],
        "html": ["smileys", "hashtags", "urls", "newlines"],
        "xhtml": ["smileys", "hashtags", "urls"]
    },
    "children": {
           "sortOrder": "chronological"
    },
    "plugins": [
        {
            "name": "Whirlpools",
            "after" : 2,
            "clickable": true
        },
        {"name": "CommunityFlag"},
        {"name": "Curation"},
        {"name": "UserBan"},
        {"name": "UserPrivileges"},
        {
            "name": "Reply",
            "actionString": "Write reply here...",
            "nestedPlugins": [{
                "name": "FormAuth",
                "submitPermissions": "forceLogin"
            }]
        }
    ]
});
</script>

<!-- End of RealTidbits.Comments -->
  