<?php
	
	require_once("vars.php");
	require_once("includes/dvrui_debug.php");

	define('TINYAJAX_PATH', '.');
	require_once("TinyAjax.php");
	require_once("TinyAjaxBehavior.php");

	require_once("rules.php");
	require_once("recordings.php");
	require_once("series.php");
	require_once("settings.php");
	require_once("theme.php");
	require_once("upcoming.php");
	require_once("search.php");
	require_once("dashboard.php");
	
	/* Prepare Ajax */
	$ajax = new TinyAjax();
	$ajax->setRequestType("POST");    // Change request-type from GET to POST
	$ajax->showLoading();             // Show loading while callback is in progress
	
	/* Export the PHP Interface */
	$ajax->exportFunction("openDashboardPage","");
	$ajax->exportFunction("openSeriesPage","");
	$ajax->exportFunction("openRulesPage","seriesid");
	$ajax->exportFunction("openRecordingsPage","seriesid");
	$ajax->exportFunction("openSettingsPage","");
	$ajax->exportFunction("openClearCache","");
	$ajax->exportFunction("openServerPage","");
	$ajax->exportFunction("openSearchPage","searchString");
	$ajax->exportFunction("openUpcomingPage","seriesid");
	$ajax->exportFunction("deleteRecordingByID","id, rerecord, seriesid");
	$ajax->exportFunction("deleteRuleByID","id");
	$ajax->exportFunction("changeRulePriority","ruleid, changeVal");
	$ajax->exportFunction("deleteRuleFromSearch","searchstring, id");
	$ajax->exportFunction("createQuickRuleFromSearch","searchString, seriesid, recentonly");
	$ajax->exportFunction("createRuleFromSearch","searchString, seriesid, recentonly, start, end, channel, recordtime, recordafter");

	/* GO */
	$ajax->process(); // Process our callback

	// Apply default Theme */
	$stylesheet = getTheme();
	
	//Build navigation menu for pages
	$pageTitles = array('Dashboard', 'Series', 'Recordings', 'Upcoming','Rules', 'Search','.');
	$pageNames = array('dashboard_page', 'series_page', 'recordings_page', 'upcoming_page', 'rules_page',  'search_page', 'settings_page');
	$menu_data = file_get_contents('style/pagemenu.html');
	$menuEntries = '';
	for ($i=0; $i < count($pageNames); $i++) {
		$menuEntry = str_replace('<!-- dvrui_menu_pagename-->',$pageNames[$i],file_get_contents('style/pagemenu_entry.html'));
		$menuEntry = str_replace('<!-- dvrui_menu_pagetitle-->',$pageTitles[$i],$menuEntry);
		$menuEntries .= $menuEntry;
	}
	$menu_data = str_replace('<!-- dvrui_pagemenu_entries-->',$menuEntries,$menu_data);
	
	// --- Build Page Here ---
	$pageName = DVRUI_Vars::DVRUI_name;
	$UIVersion = "version: " . DVRUI_Vars::DVRUI_version;
	$pagecontent = "";
	// --- include header ---
	$header = file_get_contents('style/header.html');
	$pagecontent = str_replace('[[pagetitle]]',$pageName,$header);
	$pagecontent = str_replace('<!-- stylesheet -->',$stylesheet,$pagecontent);
	$pagecontent = str_replace('<!-- tinyAjax -->',$ajax->drawJavaScript(false, true),$pagecontent);

	// --- Build Body ---
	$indexPage = file_get_contents('style/index_page.html');

	$dashboarddata = file_get_contents('style/dashboard.html');
	$rulesdata = file_get_contents('style/rules.html');
	$recordingsdata = file_get_contents('style/recordings.html');
	$seriesdata = file_get_contents('style/series.html');
	$settingsdata = file_get_contents('style/settings.html');
	$updata = file_get_contents('style/upcoming.html');
	$searchdata = file_get_contents('style/search.html');

	$indexPage = str_replace('[[pagetitle]]',$pageName,$indexPage);
	$indexPage = str_replace('[[UI-Version]]',$UIVersion,$indexPage);

	$indexPage = str_replace('<!-- dvrui_pagemenu -->',$menu_data,$indexPage);
	$indexPage = str_replace('<!-- dvrui_dashboard -->',$dashboarddata,$indexPage);
	$indexPage = str_replace('<!-- dvrui_serieslist -->',$seriesdata,$indexPage);
	$indexPage = str_replace('<!-- dvrui_recordingslist -->',$recordingsdata,$indexPage);
	$indexPage = str_replace('<!-- dvrui_upcominglist -->',$updata,$indexPage);
	$indexPage = str_replace('<!-- dvrui_ruleslist -->',$rulesdata,$indexPage);
	$indexPage = str_replace('<!-- dvrui_searchlist -->',$searchdata,$indexPage);
	$indexPage = str_replace('<!-- dvrui_settingslist -->',$settingsdata,$indexPage);
	
	// -- Attach the Index to the Page
	$pagecontent .= $indexPage;

	// --- include footer ---
	$footer = file_get_contents('style/footer.html');
	$pagecontent .= $footer;
	echo($pagecontent);

	error_log( "======= Debug Log END =========" );
?>

