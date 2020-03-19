<?php
/**
 * 
 * Author:            VSC55
 * Author URI:        https://github.com/vsc55/embed-block-for-github
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * 
 */

namespace EmbedBlockForGithub\GitHub\API;


// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class GitHub_API {

	private static $instance;
	private $parent = null;

	public $hooks_customMessageGitHub = null;

	private $github_api = "https://api.github.com";

	private $url 		= "";
	private $owner 		= "";
	private $repository = "";
	private $type_url 	= "";
	private $error 		= "";

	public $access_token 		= null;
	public $access_token_user 	= null;


	public static function get_instance($parent = null) {
		if ( is_null (self::$instance ) ) {
			self::$instance = new self ($parent);
		}
		return self::$instance;
	}
	
	public function __construct($parent = null) {
		$this->parent = (object)array();
		if ( ! is_null($parent) ) {
			$this->parent = $parent;
		}
	}

	/**
	 * Status of whether an error has occurred.
	 * 
	 * @return bool True an error has occurred, False no error detected.
	 */
	public function is_set_error() {
		return ( ! empty( $this->get_error() ) );
	}

	/**
	 * Get msg error
	 * 
	 */
	public function get_error() {
		return $this->error;
	}

	/**
	 * Get owner.
	 * 
	 */
	public function get_owner() {
		return $this->owner;
	}

	/**
	 * Get repository.
	 * 
	 */
	public function get_repository() {
		return $this->repository;
	}

	/**
	 * Get Type URL, Repo, Info User, etc... 
	 *
	 */
	public function get_type_URL() {
		return $this->type_url;
	}

	/**
	 * Get URL.
	 * 
	 */
	public function get_URL() {
		return $this->url;
	}

	/**
	 * Set URL github.
	 * 
	 * @param string URL
	 * @return bool True ok, False error.
	 */
	public function set_URL($url) {
		$this->clean_data();
		$this->error = "";
		$this->url 	 = $url;
		if (0 === $this->validate_URL()) {
			$this->get_data_by_URL();
		}
		return ( ! $this->is_set_error() );
	}

    /**
	 * Check if the URL is correct
	 * 
	 * @return integer 0 ok, other number is error code.
	 */
	private function validate_URL() {
		$url = $this->get_URL();
		$return_data = 0;
		switch(True) {
			case ( '' === trim( $url ) ):
				$this->error = "url_is_null";
				$return_data = 1;
				break;

			case ( ! filter_var( $url, FILTER_VALIDATE_URL ) ):
				$this->error = "url_not_valid";
				$return_data = 2;
				break;
			
			case ( ! preg_match('/^(?:https|http):\/\/?(?:www.)?github.com\//', $url) ):
				$this->error = "url_not_github";
				$return_data = 3;
				break;
		}
		return $return_data;
	}

	/**
	 * Detect type request, owner, repository, etc...
	 * 
	 * @return bool True ok, False error.
	 */
	private function get_data_by_URL() {
		$this->clean_data();
		
		$url 		 = $this->get_URL();
		$error 		 = array();
		$return_data = true;

		if ( empty($url) ) {
			$this->error = 'url_is_null';
			$return_data = false;
		} else {
			$slug = preg_replace('/^(?:https|http):\/\/?(?:www.)?github.com\//', '', $url);
			$arr_slug = explode("/", $slug, 2);
			
			switch ( count($arr_slug) ) {
				case 2:
					if (! empty( trim( $arr_slug[1] ) ) ) {
						$this->repository = $arr_slug[1];
					} else {
						$error['repository_is_null'] = true;
					}
				case 1:
					if (! empty( trim( $arr_slug[0] ) ) ) {
						$this->owner = $arr_slug[0];
					} else {
						$error['owner_is_null'] = true;
					}
			}
			if ( 0 === count ( $error ) ) {
				switch ( count($arr_slug) ) {
					case 1:
						/* User */
						$this->type_url = "user";
						break;

					case 2:
						/* Repo */
						$this->type_url = "repo";
						break;

					default:
						/* ??? */
						$error['type_unknown'] = true;
				}
			}
			if ( count ( $error ) > 0 ) {
				$this->error = 'url_not_valid';
				$return_data = false;
			}
		}
		return $return_data;
	}

	/**
	 * 
	 * 
	 */
	private function clean_data() {
		$this->type_url 	= "";
		$this->owner 		= "";
		$this->repository 	= "";
	}

	/**
	 * Get the URL of the GitHub API with the data obtained from the URL provided.
	 * 
	 */
	private function get_GitHub_URL( $type = "auto" ) {
		$return_data = "";
		switch ( $type ) {
			case "rate_limit":
				$return_data = $this->github_api . '/rate_limit';
				break;

			case "auto":
				switch ( $this->get_type_URL() ) {
					case "user":
						$return_data = $this->github_api . '/users/' . $this->get_owner();
						break;
		
					case "repo":
						$return_data = $this->github_api . '/repos/' . $this->get_owner() . '/' . $this->repository;
						break;			
				}
				break;
		}
		return $return_data;
	}

	/**
	 * 
	 * 
	 */
	public function get_data() {
		$return_data = NULL;
		if ( ! $this->is_set_error() ) {
			$url_api = $this->get_GitHub_URL();
			if ( ! empty( $url_api ) ) {
				$response = $this->call_API( $url_api );
				$return_data = json_decode( wp_remote_retrieve_body( $response ) );
				if ( is_wp_error( $results ) || ! isset( $results['response']['code'] ) || $results['response']['code'] != '200' ) {
					//$error = "info_no_available";
					//TODO: Pendiente mirar $response
					//$response->get_error_message()
				}

				/* We check if any error has been received from github. */
				if ( isset( $return_data->message ) )
				{
					$this->error = 'get_error_from_github';
					if ( ! is_null( $this->hooks_customMessageGitHub) ) {
						$return_data->message = call_user_func($this->hooks_customMessageGitHub, $return_data->message, $return_data->documentation_url);
					}
				}
			}
		}
		return $return_data;
	}

	/**
	 * 
	 */
	public function call_API($url) {
		$args = array();
		$args['user-agent'] = 'Plugin WordPress - embed-block-for-github - https://github.com/vsc55/embed-block-for-github';
		if ( (! empty( $this->access_token_user) ) && (! empty($this->access_token) ) ) {
			$args['headers'] = [
				'Authorization' => 'Basic ' . base64_encode( $this->access_token_user . ':' . $this->access_token ),
			];
		}
		$results = wp_remote_get($url, $args);
		if ( is_wp_error( $results ) || ! isset( $results['response']['code'] ) || $results['response']['code'] != '200' ) {
			//TODO: Pendiente mirar si se hace algo con get_error_message
			//echo $results->get_error_message();
		}
		return $results;
	}

	/**
	 * 
	 */
	public function get_rate($json_decode = true) {
		$url_api = $this->get_GitHub_URL("rate_limit");
		$data = $this->call_API( $url_api );
		if ($json_decode) {
			$data = json_decode( wp_remote_retrieve_body( $data ) );
		}
		return $data;
	}
}