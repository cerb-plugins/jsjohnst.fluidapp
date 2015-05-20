<?php
class FluidAppDataPlugin {
	static public function getTemplateHandler() {
		$worker = CerberusApplication::getActiveWorker();
		$tpl = DevblocksPlatform::getTemplateService();

		// Preferences

		$badge_enabled = DAO_WorkerPref::get($worker->id, 'fluidapp.badge_enabled', 1);
		$tpl->assign('badge_enabled', $badge_enabled);

		if($badge_enabled) {
			$counts = DAO_FluidApp::getBadgeCounts($worker->id);
			$tpl->assign('badge_count', $counts);
		}

		$growl_enabled = DAO_WorkerPref::get($worker->id, 'fluidapp.growl_enabled', 1);
		$tpl->assign('growl_enabled', $growl_enabled);

		if($growl_enabled) {
			$notifications = DAO_FluidApp::getNotifications($worker->id);
			$alerts = array();

			// Sticky patterns
			@$growl_sticky_patterns = DevblocksPlatform::parseCrlfString(DAO_WorkerPref::get($worker->id, 'fluidapp.growl_sticky_patterns', ''));
			
			// Build an abstract notification array
			if(!empty($notifications))
			foreach($notifications as $event) {
				$entry = json_decode($event[SearchFields_Notification::ENTRY_JSON], true);
				$message = CerberusContexts::formatActivityLogEntry($entry,'text');
				$is_sticky = false;
				
				// Does the worker want this substring to be sticky in Growl?
				if(is_array($growl_sticky_patterns))
				foreach($growl_sticky_patterns as $pattern) {
					if(false !== stripos($message, $pattern)) {
						$is_sticky = true;
						break;
					}
				}
				
				$url = null;
				
				if(isset($entry['urls']) && is_array($entry['urls']))
					@$url = current($entry['urls']);
				
				// Push
				$alerts[] = array(
					'id' => $event[SearchFields_Notification::ID],
					'title' => $message,
					'description' => '',
					'url' => $url,
					'is_sticky' => $is_sticky,
				);
			}
			
			$tpl->assign("notifications_json", json_encode($alerts));
		}

		return $tpl;
	}
	
};

class FluidAppDataPreBodyRenderer extends Extension_AppPreBodyRenderer {
    function render() {
    	
        // Only show our notifications when someone is logged in
        $worker = CerberusApplication::getActiveWorker();
        if(empty($worker))
        	return;

        $tpl = FluidAppDataPlugin::getTemplateHandler();

        $tpl->display('devblocks:jsjohnst.fluidapp::prebody.tpl');
    }
	
};

class FluidAppDataAPIFetch extends DevblocksControllerExtension {
    public function handleRequest(DevblocksHttpRequest $response) {
        $tpl = FluidAppDataPlugin::getTemplateHandler();

        header("Content-Type: text/javascript");
		
        $tpl->display('devblocks:jsjohnst.fluidapp::jsonp.tpl');
	}
	
};

class DAO_FluidApp {
	static public function getBadgeCounts($worker_id) {
		$badge_type = DAO_WorkerPref::get($worker_id, 'fluidapp.badge_type', 'notifications');
        $count = 0;
		
		switch($badge_type) {
			default:
			case 'notifications':
				$count = intval(DAO_Notification::getUnreadCountByWorker($worker_id));
				break;
		}
		
		return $count;
	}
	
	static public function getNotifications($worker_id) {
        if(null == ($worker = DAO_Worker::get($worker_id)))
			return null;
			
		if(!isset($_SESSION["fluid_latest_seen"])) {
			$newest = 0;
			
			list($notifications, $null) = DAO_Notification::search(
				array(),
				array(
					SearchFields_Notification::WORKER_ID => new DevblocksSearchCriteria(SearchFields_Notification::WORKER_ID,'=',$worker->id),
					SearchFields_Notification::IS_READ => new DevblocksSearchCriteria(SearchFields_Notification::IS_READ,'=',0),
				),
				1,
				0,
				SearchFields_Notification::ID,
				false,
				false
			);
			
			if(!empty($notifications)) {
				$last_notification = end($notifications);
				$newest = intval($last_notification[SearchFields_Notification::ID]);
			}
			
		} else {
			$newest = $_SESSION["fluid_latest_seen"];
			
		}
			
		list($notifications, $null) = DAO_Notification::search(
			array(),
			array(
				SearchFields_Notification::ID => new DevblocksSearchCriteria(SearchFields_Notification::ID,'>',$newest),
				SearchFields_Notification::WORKER_ID => new DevblocksSearchCriteria(SearchFields_Notification::WORKER_ID,'=',$worker->id),
				SearchFields_Notification::IS_READ => new DevblocksSearchCriteria(SearchFields_Notification::IS_READ,'=',0),
			),
			5,
			0,
			SearchFields_Notification::ID,
			true,
			false
		);

		if(!empty($notifications)) {
			$last_notification = end($notifications);
			
			// Keep a seek pointer
			$newest = intval($last_notification[SearchFields_Notification::ID]);
		}

		$_SESSION["fluid_latest_seen"] = $newest;

		return $notifications;
	}
	
};

class FluidAppPreferences extends Extension_PreferenceTab {
	// Ajax
	function showTab() {
		$worker = CerberusApplication::getActiveWorker();

		$tpl = DevblocksPlatform::getTemplateService();
		
		// Load worker pref for badge count
		$badge_enabled = DAO_WorkerPref::get($worker->id, 'fluidapp.badge_enabled', 1);
		$tpl->assign('badge_enabled', $badge_enabled);

		$growl_enabled = DAO_WorkerPref::get($worker->id, 'fluidapp.growl_enabled', 1);
		$tpl->assign('growl_enabled', $growl_enabled);

		$growl_sticky_patterns = DAO_WorkerPref::get($worker->id, 'fluidapp.growl_sticky_patterns', '');
		$tpl->assign('growl_sticky_patterns', $growl_sticky_patterns);
		
		$tpl->display('devblocks:jsjohnst.fluidapp::prefs.tpl');
	}
	
	// Post
	function saveTab() {
		$worker = CerberusApplication::getActiveWorker();
		
		// Save the prefs from the form
		@$badge_enabled = DevblocksPlatform::importGPC($_POST['badge_enabled'],'integer',0);
		DAO_WorkerPref::set($worker->id, 'fluidapp.badge_enabled', $badge_enabled);

		@$growl_enabled = DevblocksPlatform::importGPC($_POST['growl_enabled'],'integer',0);
		DAO_WorkerPref::set($worker->id, 'fluidapp.growl_enabled', $growl_enabled);

		// Sticky patterns
		@$growl_sticky_patterns = DevblocksPlatform::importGPC($_POST['growl_sticky_patterns'],'string','');
		DAO_WorkerPref::set($worker->id, 'fluidapp.growl_sticky_patterns', $growl_sticky_patterns);
		
		DevblocksPlatform::setHttpResponse(new DevblocksHttpResponse(array('preferences','fluidapp')));
	}
 
};

