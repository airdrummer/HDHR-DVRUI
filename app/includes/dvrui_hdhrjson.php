<?php
	require_once("includes/dvrui_common.php");
	require_once("vars.php");

class DVRUI_HDHRjson {
	private $myhdhrurl = DVRUI_Vars::DVRUI_apiurl . 'discover';
	private $hdhrkey_devID = 'DeviceID';
	private $hdhrkey_localIP = 'LocalIP';
	private $hdhrkey_baseURL = 'BaseURL';
	private $hdhrkey_discoverURL = 'DiscoverURL';
	private $hdhrkey_lineupURL = 'LineupURL';
	private $hdhrkey_modelNum = 'ModelNumber';
	private $hdhrkey_modelName = 'FriendlyName';
	private $hdhrkey_auth = 'DeviceAuth';
	private $hdhrkey_fwVer = 'FirmwareVersion';
	private $hdhrkey_Ver = 'Version';
	private $hdhrkey_fwName = 'FirmwareName';
	private $hdhrkey_tuners = 'TunerCount';
	private $hdhrkey_free = 'FreeSpace';
	private $auth = '';
	private $hdhrkey_storageID = 'StorageID';
	private $hdhrkey_storageURL = 'StorageURL';

	private $hdhrkey_legacy = 'Legacy';

	private $hdhrlist = array();
	private $enginelist = array();
	private $hdhrlist_key_channelcount = 'ChannelCount';
	
	public function DVRUI_HDHRjson() {

		if(DVRUI_Vars::DVRUI_discover_cache != ''){
			$cachesecs = DVRUI_Vars::DVRUI_discover_cache;
		}else{
			$cachesecs = 60;
		}

		$hdhr_data = getCachedJsonFromUrl($this->myhdhrurl,$cachesecs);
		for ($i=0;$i<count($hdhr_data);$i++) {
			$hdhr = $hdhr_data[$i];
			$hdhr_base = $hdhr[$this->hdhrkey_baseURL];
			$hdhr_ip = $hdhr[$this->hdhrkey_localIP];
			
			if (!array_key_exists($this->hdhrkey_discoverURL,$hdhr)) {
				// Skip this HDHR - it doesn't support the newer HTTP interface
				// for DVR
				continue;
			}

			$hdhr_info = getCachedJsonFromUrl($hdhr[$this->hdhrkey_discoverURL],$cachesecs);

			if (array_key_exists($this->hdhrkey_storageURL,$hdhr)) {
				// this is a record engine!

				// Need to confirm it's a valid one - After restart of
				// engine it updates my.hdhomerun.com but sometimes the
				// old engine config is left behind.
				$rEngine = getCachedJsonFromUrl($hdhr[$this->hdhrkey_discoverURL],$cachesecs);
				if (strcmp($rEngine[$this->hdhrkey_storageID],$hdhr[$this->hdhrkey_storageID]) != 0) {
					//skip, this is not a valid engine
					continue;
				}
				
				$this->storageURL = $hdhr[$this->hdhrkey_storageURL];
				$this->enginelist[] = array ($this->hdhrkey_storageID => $hdhr[$this->hdhrkey_storageID],
									$this->hdhrkey_modelName => $hdhr_info[$this->hdhrkey_modelName],
									$this->hdhrkey_Ver => $hdhr_info[$this->hdhrkey_Ver],
									$this->hdhrkey_free => $hdhr_info[$this->hdhrkey_free],
									$this->hdhrkey_localIP => $hdhr[$this->hdhrkey_localIP],
									$this->hdhrkey_baseURL => $hdhr[$this->hdhrkey_baseURL],
									$this->hdhrkey_discoverURL => $hdhr[$this->hdhrkey_discoverURL],
									$this->hdhrkey_storageURL => $hdhr[$this->hdhrkey_storageURL]);

				continue;
			}
			// ELSE we have a truner
		
			$tuners='unknown';
			if (array_key_exists($this->hdhrkey_tuners,$hdhr_info)) {
				$tuners = $hdhr_info[$this->hdhrkey_tuners];
			}

			$legacy='No';
			if (array_key_exists($this->hdhrkey_legacy,$hdhr_info)) {
				$legacy = $hdhr_info[$this->hdhrkey_legacy];
			}

			$hdhr_lineup = getCachedJsonFromUrl($hdhr_info[$this->hdhrkey_lineupURL],$cachesecs);	

			$this->hdhrlist[] = array( $this->hdhrkey_devID => $hdhr[$this->hdhrkey_devID],
										$this->hdhrkey_modelNum => $hdhr_info[$this->hdhrkey_modelNum],
										$this->hdhrlist_key_channelcount => count($hdhr_lineup),
										$this->hdhrkey_baseURL => $hdhr_base,
										$this->hdhrkey_lineupURL => $hdhr_info[$this->hdhrkey_lineupURL],
										$this->hdhrkey_modelName => $hdhr_info[$this->hdhrkey_modelName],
										$this->hdhrkey_auth =>$hdhr_info[$this->hdhrkey_auth],
										$this->hdhrkey_fwVer => $hdhr_info[$this->hdhrkey_fwVer],
										$this->hdhrkey_tuners => $tuners,
										$this->hdhrkey_legacy => $legacy,
										$this->hdhrkey_fwName => $hdhr_info[$this->hdhrkey_fwName]);
			$this->auth .= $hdhr_info[$this->hdhrkey_auth];
		}
	}

	public function get_auth() {
		return $this->auth;
	}
	
	public function device_count() {
		return count($this->hdhrlist);
	}
	public function engine_count() {
		return count($this->enginelist);
	}
	public function get_engine_base_url($pos){
		$engine = $this->enginelist[$pos];
		return $engine[$this->hdhrkey_baseURL];
	}
	public function get_engine_storage_url($pos){
		$engine = $this->enginelist[$pos];
		return $engine[$this->hdhrkey_storageURL];
	}
	
	public function get_engine_storage_id($pos){
		$engine = $this->enginelist[$pos];
		return $engine[$this->hdhrkey_storageID];
	}
	
	public function get_engine_local_ip($pos){
		$engine = $this->enginelist[$pos];
		return $engine[$this->hdhrkey_localIP];
	}
	
	public function get_engine_image($pos) {
		return "./images/HDHR-DVR.png";
	}

	public function get_engine_name($pos) {
		return $this->enginelist[$pos][$this->hdhrkey_modelName];
	}
	
	public function get_engine_version($pos) {
		return $this->enginelist[$pos][$this->hdhrkey_Ver];
	}
	
	public function get_engine_space($pos) {
		$free = $this->enginelist[$pos][$this->hdhrkey_free];
		if ($free > 1e+12 ) {
			return number_format($free/1e+12,2) . ' TB';
		} else if ($free > 1e+9) {
			return number_format($free/1e+9,2) . ' GB';
		} else if ( $free >  1e+6) {
			return number_format($free/1e+6,2) . ' MB';
		} else if ( $free > 1e+3){
			return number_format($free/1e+3,2) . ' kB';
		} else {
			return number_format($free,2) . ' B';
		}
	}
	
	public function get_engine_discoverURL($pos) {
		return $this->enginelist[$pos][$this->hdhrkey_discoverURL];
	}

	public function poke_engine($pos) {
		$url = $this->enginelist[$pos][$this->hdhrkey_baseURL] . "/recording_events.post?sync";
		getJsonFromUrl($url);
	}

	public function get_device_id($pos) {
		$device = $this->hdhrlist[$pos];
		return $device[$this->hdhrkey_devID];
	}

	public function get_device_model($pos) {
		$device = $this->hdhrlist[$pos];
		return $device[$this->hdhrkey_modelNum];
	}

	public function get_device_channels($pos) {
		$device = $this->hdhrlist[$pos];
		return $device[$this->hdhrlist_key_channelcount];
	}

	public function get_device_lineup($pos) {
		$device = $this->hdhrlist[$pos];
		return $device[$this->hdhrkey_lineupURL];
	}

	public function get_device_baseurl($pos) {
		$device = $this->hdhrlist[$pos];
		return $device[$this->hdhrkey_baseURL];
	}

	public function get_device_firmware($pos) {
		$device = $this->hdhrlist[$pos];
		return $device[$this->hdhrkey_fwVer];
	}

	public function get_device_tuners($pos) {
		$device = $this->hdhrlist[$pos];
		if (array_key_exists($this->hdhrkey_tuners,$device)) {
			return $device[$this->hdhrkey_tuners];
		} else {
			return '??';
		}
	}

	public function get_device_legacy($pos) {
		$device = $this->hdhrlist[$pos];
		if( $device[$this->hdhrkey_legacy] == 1) {
			return 'Legacy';
		}
		else {
			return 'HTTP';
		}
	}

	public function get_device_auth($pos) {
		$device = $this->hdhrlist[$pos];
		if (array_key_exists($this->hdhrkey_auth,$device)) {
			return $device[$this->hdhrkey_auth];
		} else {
			return '??';
		}
	}
	
	public function get_device_image($pos) {
		$device = $this->hdhrlist[$pos];
		switch ($device[$this->hdhrkey_modelNum]) {
			case 'HDTC-2US':
				return './images/HDTC-2US.png';
			case 'HDHR3-CC':
				return './images/HDHR3-CC.png';
			case 'HDHR3-4DC':
				return './images/HDHR3-4DC.png';
			case 'HDHR3-EU':
			case 'HDHR3-US':
			case 'HDHR3-DT':
				return './images/HDHR3-US.png';
			case 'HDHR4-2US':
			case 'HDHR4-2DT':
				return './images/HDHR4-US.png';
			case 'HDHR5-DT':
			case 'HDHR5-2US':
			case 'HDHR5-4DC':
			case 'HDHR5-4DT':
			case 'HDHR5-4US':
				return './images/HDHR5-US.png';
			case 'HDHR5-6CC':
				return './images/HDHR5-6CC.png';
			case 'TECH4-2DT':
			case 'TECH4-2US':
				return './images/TECH4-2US.png';
			case 'TECH4-8US':
				return './images/TECH4-8US.png';
			case 'TECH5-36CC':
				return './images/TECH5-36CC.png';
			case 'TECH5-16DC':
			case 'TECH5-16DT':
				return './images/TECH5-16DT.png';
			default:
				return './images/HDHR5-US.png';
		}
	}

}
?>
