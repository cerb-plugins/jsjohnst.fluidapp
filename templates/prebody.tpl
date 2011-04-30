<script>
var fluid_cerberus_webpath = "{devblocks_url}{/devblocks_url}";

{literal}
if(window.fluid) {
	window.fluid.addDockMenuItem("Mail", function() { document.location.href = fluid_cerberus_webpath + "tickets"; });
	window.fluid.addDockMenuItem("Send Mail", function() { document.location.href = fluid_cerberus_webpath + "tickets/compose"; });
	window.fluid.addDockMenuItem("Open Ticket", function() { document.location.href = fluid_cerberus_webpath + "tickets/create"; });
	window.fluid.addDockMenuItem("Activity", function() { document.location.href = fluid_cerberus_webpath + "activity"; });
	window.fluid.addDockMenuItem("Address Book", function() { document.location.href = fluid_cerberus_webpath + "contacts"; });
	window.fluid.addDockMenuItem("My Profile", function() { document.location.href = fluid_cerberus_webpath + "profiles/workers/me"; });
	window.fluid.addDockMenuItem("My Settings", function() { document.location.href = fluid_cerberus_webpath + "preferences"; });

	function loadFluidJSONPUrl(url) {
		var headID = document.getElementsByTagName("head")[0];         
		var newScript = document.createElement('script');
		    newScript.type = 'text/javascript';
		    newScript.src = url;
		    headID.appendChild(newScript);
	}
	
	//loadFluidJSONPUrl(fluid_cerberus_webpath + "fluidapp.jsonp?v=" + (new Date()).getTime()); }, 0);
	window.setInterval(function() { loadFluidJSONPUrl(fluid_cerberus_webpath + "fluidapp.jsonp?v=" + (new Date()).getTime()); }, 60 * 1000);	
}
{/literal}

//Pull the JSON once immediately
{include file="devblocks:jsjohnst.fluidapp::jsonp.tpl"}

</script>
