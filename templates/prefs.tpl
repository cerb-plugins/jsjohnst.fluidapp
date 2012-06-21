<fieldset class="peek">
<legend>Fluid.app Integration</legend>

<form action="{devblocks_url}{/devblocks_url}" method="post">
<input type="hidden" name="c" value="preferences">
<input type="hidden" name="a" value="saveTab">
<input type="hidden" name="ext_id" value="jsjohnst.fluidapp.pref">
	
<b>Dock:</b><br>
<label><input type="checkbox" name="badge_enabled" value="1" {if $badge_enabled}checked="checked"{/if}> Show Unread Notifications on Dock Icon</label>
<br>
<br>

<b>Growl:</b><br>
<label><input type="checkbox" name="growl_enabled" value="1" {if $growl_enabled}checked="checked"{/if}> Show Growl Notifications</label>
<blockquote style="margin-left:20px;">
	<b>Make sticky if notification contains the following text:</b> <i>(one per line)</i><br>
	<textarea cols="65" rows="5" name="growl_sticky_patterns">{$growl_sticky_patterns}</textarea><br>
</blockquote>
<br> 	

<button type="submit"><span class="cerb-sprite2 sprite-tick-circle"></span> {$translate->_('common.save_changes')|capitalize}</button>
</form>

</fieldset>