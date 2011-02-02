var fluid_cerb4_badge_enabled = {$badge_enabled};
var fluid_cerb4_growl_enabled = {$growl_enabled};
{if !empty($badge_count)}
	var fluid_cerb4_badge_count = {$badge_count};
{/if}
var fluid_cerb4_webpath = "{devblocks_url}{/devblocks_url}";
{if !empty($notifications_json)}
	var fluid_cerb4_notifications = {$notifications_json};
{/if}

{literal}
if (window.fluid) {
	if(fluid_cerb4_badge_enabled && fluid_cerb4_badge_count)
		window.fluid.dockBadge = fluid_cerb4_badge_count;
	
	if(fluid_cerb4_growl_enabled) {
		if(null != fluid_cerb4_notifications)
		for(var i in fluid_cerb4_notifications) {
			window.fluid.showGrowlNotification({
				title: fluid_cerb4_notifications[i].title,
				description: fluid_cerb4_notifications[i].description,
				priority: 1,
				sticky: fluid_cerb4_notifications[i].is_sticky,
				identifier: fluid_cerb4_notifications[i].id,
				onclick: function() {
					document.location.href = fluid_cerb4_webpath + "preferences/events/";
					//document.location.href = fluid_cerb4_notifications[i].url;
				} 
			});
		}
	} 
}
{/literal}