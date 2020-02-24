<?php

namespace EmbedBlockForGithub\GitHub\API;


// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class GitHubAPI {

	public $parent = null;
	public $hooks_customMessageGitHub = null;

	private static $instance;

	private $github_url = "https://github.com";
	private $github_api = "https://api.github.com";

	public $url = "";
	private $owner = "";
	private $repository = "";
	private $type_url = "";
	private $error = "";

	public static function get_instance($parent = null) {
		if ( is_null (self::$instance ) ) {
			self::$instance = new self;
		}
		if ( ! is_null( $parent ) ) {
			self::$instance->parent = $parent;
		}
		return self::$instance;
	}
	
	public function __construct() {
		$this->parent = (object)array();
	}

	/**
	 * Status of whether an error has occurred.
	 * 
	 * @return bool True an error has occurred, False no error detected.
	 */
	public function isSetError(){
		return ( ! empty($this->getError()) );
	}

	/**
	 * Get msg error
	 * 
	 */
	public function getError() {
		return $this->error;
	}

	/**
	 * Get owner.
	 * 
	 */
	public function getOwner(){
		return $this->owner;
	}

	/**
	 * Get repository.
	 * 
	 */
	public function getRepository(){
		return $this->repository;
	}

	/**
	 * Get Type URL, Repo, Info User, etc... 
	 *
	 */
	public function getTypeURL() {
		return $this->type_url;
	}

	/**
	 * Get URL.
	 * 
	 */
	public function getURL() {
		return $this->url;
	}

	/**
	 * Set URL github.
	 * 
	 * @param string URL
	 * @return bool True ok, False error.
	 */
	public function setURL($url) {
		$this->cleanData();
		$this->error = "";
		$this->url = $url;
		if ($this->validateURL() == 0) {
			$this->getDataByURL();
		}
		return ( ! $this->isSetError() );
	}

    /**
	 * Check if the URL is correct
	 * 
	 * @return integer 0 ok, other number is error code.
	 */
	private function validateURL() {
		$url = $this->getURL();
		$return_data = 0;
		switch(True) {
			case ( '' === trim( $url ) ):
				$this->error = "url_is_null";
				$return_data = 1;
			break;

			case (! filter_var( $url, FILTER_VALIDATE_URL ) ):
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
	private function getDataByURL() {
		$this->cleanData();
		$url = $this->getURL();
		$error = array();
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
			if ( count ( $error ) == 0 ) {
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
	private function cleanData(){
		$this->type_url = "";
		$this->owner = "";
		$this->repository = "";
	}

	/**
	 * Get the URL of the GitHub API with the data obtained from the URL provided.
	 * 
	 */
	private function getGitHubURL(){
		$return_data = "";
		switch ($this->getTypeURL())
		{
			case "user":
				$return_data = $this->github_api.'/users/'.$this->owner;
				break;

			case "repo":
				$return_data = $this->github_api.'/repos/'.$this->owner.'/'.$this->repository;
				break;			
		}
		return $return_data;
	}

	/**
	 * 
	 * 
	 */
	public function getData() {
		$return_data = NULL;
		if (! $this->isSetError()) {
			$url_api = $this->getGitHubURL();
			if (! empty( $url_api ) ) {
				$response = wp_remote_get( $url_api );
				$return_data = json_decode( wp_remote_retrieve_body( $response ) );
				/*
				if (is_wp_error( $response ) ) {
					$error = "info_no_available";
					//TODO: Pendiente mirar $response
					//$response->get_error_message()
				}
				*/

				/* We check if any error has been received from github. */
				if ( isset( $return_data->message ) )
				{
					$this->error = 'get_error_from_github';
					if (! is_null( $this->hooks_customMessageGitHub) ) {
						$return_data->message = call_user_func($this->hooks_customMessageGitHub, $return_data->message, $return_data->documentation_url);
					}
				}
			}
		}
		return $return_data;
	}

	
	
}