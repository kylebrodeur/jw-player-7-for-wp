<?php
/**
 * The JW Player API for communicate  with the dashboard
 * @author ilGhera
 * @package jw-player-7-for-wp/classes
 * @version 1.6.0
 */
class jwppp_dasboard_api {

	public function __construct() {

		$this->api_key = get_option('jwppp-api-key');
		$this->api_secret = get_option('jwppp-api-secret');

		$this->api = $this->init();

	}	

	public function args_check() {
		if($this->api_key && $this->api_secret) {
			return true;
		}
		return false;
	}	

	public function init() {

		$botr_api = null;
		if(strlen($this->api_key) === 8 && strlen($this->api_secret) === 24) {
			$botr_api = new BotrAPI($this->api_key, $this->api_secret);			
		}
		return $botr_api;
	}

	public function call($url) {
		global $wp_version;
		$output = wp_remote_get(
			$url, 
			array(
				'timeout' => 5,
			    'user-agent'  => 'WordPress/' . $wp_version . ' JWPlayerForWordPressVIP/' . JWPPP_VERSION . ' PHP/' . phpversion()
			)
		);

		if(is_array($output)) {
			if($output['response']['code'] !== 200) {

				$body = unserialize($output['body']);
		
				return array('error' => $body['message']);
		
			} else {

				return unserialize($output['body']);

			}
		}
	}


	public function search($term, $playlist = false) {


		if($this->api) {
			if($playlist) {
				$url = $this->api->call_url("channels/list", array('search' => $term, 'result_limit' => 15));
				$key = 'channels';
			} else {
				$url = $this->api->call_url("videos/list", array('search' => $term, 'result_limit' => 15, 'order_by' => 'date:desc'));	
				$key = 'videos';							
			}
			$output = $this->call($url);
			if(isset($output[$key])) {
				return $output[$key];				
			} else {
				return $output;
			}
		}
	}

	public function get_videos($media_id = null) {
		if($this->api) {


			$parameters = array('result_limit' => 15, 'order_by' => 'date:desc');
			if($media_id) {
				$parameters['video_keys_filter'] = $media_id;
			}

			$url = $this->api->call_url("videos/list", $parameters); //videos					
			$output = $this->call($url);

			if(isset($output['videos'])) {
				return $output['videos'];				
			} else {
				return $output;
			}
		}
	}

	public function get_playlists($media_id = null) {
		if($this->api) {


			$parameters = array('result_limit' => 5);
			if($media_id) {
				$parameters['search'] = $media_id;
			}

			$url = $this->api->call_url("channels/list", $parameters);
			$output = $this->call($url);

			if(isset($output['channels'])) {
				return $output['channels'];
			} else {
				return $output;
			}
		}
	}

	public function account_validation() {


		if($this->api) {
			$url = $this->api->call_url("accounts/show", array('account_key' => $this->api_key)); //videos
			$output = $this->call($url);
			

			if(isset($output['status']) && $output['status'] === 'ok') {
				return true;					
			} else {
				return $output;
			}
		} else {
			return false;			
		}
	}

	public function get_players() {


		if($this->api) {
			$url = $this->api->call_url("players/list"); //videos			
			$output = $this->call($url);

			if(isset($output['players'])) {
				return $output['players'];					
			} else {
				return $output;
			}
		}
	}

}