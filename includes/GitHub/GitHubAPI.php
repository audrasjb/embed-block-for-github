<?php

namespace EmbedBlockForGithub\GitHub\API;


// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class GitHubAPI {

    private $github_url = "https://github.com";
    private $github_api = "https://api.github.com";

    public $url = "";


    private $owner = "";
    private $repository = "";






    public function detect_url ($url) 
    {
        if ($this->check_github_url($url) != 0) {
            
        }

    }

    
	/* Check if the URL is correct */
	private function check_github_url($github_url) {
		switch(True) {
			case ( '' === trim( $github_url ) ):
				return 1;
			break;

			case (! filter_var( $github_url, FILTER_VALIDATE_URL ) ):
				return 2;
			break;
			
			case ( strpos( $github_url, 'https://github.com/' ) !== 0 ):
				return 3;
			break;
		}
		return 0;
	}

	/* Detect type request (user, repo, etc...) */
	private function detect_request($github_url) {
		$slug = str_replace( 'https://github.com/', '', $github_url );
		
		$data_return = [];
		switch ( count(explode("/", $slug)) )
		{
			case 1:
				/* User */
				$data_return['request'] = wp_remote_get( 'https://api.github.com/users/' . explode("/", $slug)[0] );
				$data_return['type'] = "user";
				break;

			case 2:
				/* Repo */
				$data_return['request'] = wp_remote_get( 'https://api.github.com/repos/' . $slug );
				$data_return['type'] = "repo";
				break;

			default:
				/* ??? */
				/*
				$data_return['request'] = "";
				$data_return['type'] = "";
				*/
				$data_return = NULL;
		}
		return $data_return;

	}





}