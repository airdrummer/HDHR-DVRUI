<?php
	require_once("TinyAjaxBehavior.php");
	require_once("vars.php");
	require_once("tools/lessc.inc.php");
	require_once("includes/Mobile_Detect.php");

	function applyDefaultTheme() {
		$less = new lessc();
		try {
			$less->checkedCompile("./style/main.less","./style/style.css");
			$less->checkedCompile("./style/m_main.less","./style/m_style.css");
		} catch (exception $e) {
			echo ($e->getMessage());
		}
	}

	function getTheme() {
		$detect = new Mobile_Detect;
		applyDefaultTheme();

		if ($detect->isMobile()) {
			return "style/m_style.css";
		}else{	
			return "style/style.css";
		}
	}
?>
