<?php
/**
 * 
 * eventon update and licensing class
 *
 * @author 		AJDE
 * @category 	Admin
 * @package 	EventON/Classes
 * @version     0.1
 */
 
class evo_updater{
   
	/** The plugin current version*/
    public $current_version;
	
    /** The plugin remote update path */
    public $api_url;

    /** Plugin Slug (plugin_directory/plugin_file.php) */
    public $plugin_slug;

    /** Plugin name (plugin_file) */
    public $slug;
	
	public $transient;

    /**
     * Initialize a new instance of the WordPress Auto-Update class
     */
    function __construct($current_version, $api_url, $plugin_slug){
        // Set the class public variables
        $this->current_version = $current_version;
        $this->api_url = $api_url;
        $this->plugin_slug = $plugin_slug;
        list ($t1, $t2) = explode('/', $plugin_slug);
        $this->slug = str_replace('.php', '', $t2);

        // define the alternative API for updating checking
        add_filter('pre_set_site_transient_update_plugins', array(&$this, 'check_update'));

        // Define the alternative response for information checking
        add_filter('plugins_api', array(&$this, 'plugins_api_call'), 10, 3);
		
		// update to current version
		$this->save_new_license_field_values('current_version',$this->current_version,$this->slug);
				
    }

    /**
     * Add our self-hosted autoupdate plugin to the filter transient
     */
    public function check_update($transient){       
		
        // Get the remote version
        $remote_version = $this->getRemote_version();

        // If a newer version is available, add the update
        if (version_compare($this->current_version, $remote_version, '<')) {
            $obj = new stdClass();
            $obj->slug = $this->slug;
            $obj->new_version = $remote_version;
            $obj->url = $this->api_url;
            $obj->package = $this->get_package_download_url();
            $transient->response[$this->plugin_slug] = $obj;
						
        }
		
		// save update status
		$_new_update = (version_compare($this->current_version, $remote_version, '<'))?true:false;	
		$this->save_new_update_details($remote_version, $_new_update, $this->current_version);
	
		return $transient;
		
    }
	
	
	
    /**
     * Add our self-hosted description to the filter
     */
    public function plugins_api_call($false, $action, $args){
		global $wp_version; 
		
		if (!isset($args->slug) || ($args->slug != $this->slug))
			return false;
		
		/*
		$plugin_info = get_site_transient('update_plugins');
		$current_version = $plugin_info->checked[$this->plugin_slug];
		*/
		$args->version = $this->current_version;
		
		$request_string = array(
				'body' => array(
					'action' => $action, 
					'request' => serialize($args),
					'api-key' => md5(get_bloginfo('url'))
				),
				'user-agent' => 'WordPress/' . $wp_version . '; ' . get_bloginfo('url')
			);
		
		$request = wp_remote_post($this->api_url, $request_string);
		
		/*
		if (is_wp_error($request) || wp_remote_retrieve_response_code($request) !== 200) {
			$res = new WP_Error('plugins_api_failed', __('An Unexpected HTTP Error occurred during the API request.</p> <p><a href="?" onclick="document.location.reload(); return false;">Try again</a>'), $request->get_error_message());
		} else {
		*/
		if (!is_wp_error($request) || wp_remote_retrieve_response_code($request) === 200) {
			$res = unserialize($request['body']);			
			if ($res === false){
				$res = new WP_Error('plugins_api_failed', __('An unknown error occurred'), $request['body']);
			}else{			
				$res->download_link = $this->get_package_download_url();
			}
		}
		
		return $res;	
		
    }
	
	
	/**
	 *	Update field values to licenses
	 */
	function save_new_update_details($remote_version, $has_new_update, $current_version){
		$licenses =get_option('_evo_licenses');
		
		if(!empty($licenses) && count($licenses)>0 && !empty($licenses[$this->slug]) ){
			
			$newarray = array();
			
			$this_license = $licenses[$this->slug];
			
			foreach($this_license as $field=>$val){		
				if($field !='remote_version' || $field!='has_new_update' ){
					$newarray[$field]=$val;
				}
			}
			$newarray['remote_version']=$remote_version;
			$newarray['has_new_update']=$has_new_update;
			
			$new_ar[$this->slug] = $newarray;
			
			$merged=array_merge($licenses,$new_ar);
						
			update_option('_evo_licenses',$merged);
			
			return $newarray;
		}else{
			return false;
		}
	}
	
	function save_new_license_field_values($license_field, $new_value, $license_slug){
		$licenses =get_option('_evo_licenses');
		
		if(!empty($licenses) && count($licenses)>0 && !empty($licenses[$license_slug]) ){
			
			$newarray = array();
			
			$this_license = $licenses[$license_slug];
			
			foreach($this_license as $field=>$val){		
				if($field !=$license_field){
					$newarray[$field]=$val;
				}
			}
			$newarray[$license_field]=$new_value;
			
			$new_ar[$license_slug] = $newarray;
			
			$merged=array_merge($licenses,$new_ar);
						
			update_option('_evo_licenses',$merged);
		}
	}
	
    /**
     * Return the remote version
     */
    public function getRemote_version(){
		global $wp_version;
		
		$args = array('slug' => $this->slug);
		$request_string = array(
			'body' => array(
				'action' => 'evo_latest_version', 
				'request' => serialize($args),
				'api-key' => md5(get_bloginfo('url'))
			),
			'user-agent' => 'WordPress/' . $wp_version . '; ' . get_bloginfo('url')
		);
		
	
        $request = wp_remote_post($this->api_url, $request_string);
        if (!is_wp_error($request) || wp_remote_retrieve_response_code($request) === 200) {
            return $request['body'];
        }
        return false;
    }
	
	
	/** get download url **/
	function get_package_download_url(){
		$license = $this->get_saved_license_key();
		
		if(''==$license){
			return false;
		}else{
			global $wp_version;
		
			$args = array(
				'slug' => $this->slug,
				'key'=>$license
			);
			$request_string = array(
				'body' => array(
					'action' => 'get_download_link', 
					'request' => serialize($args),
					'api-key' => md5(get_bloginfo('url'))
				),
				'user-agent' => 'WordPress/' . $wp_version . '; ' . get_bloginfo('url')
			);
			
		
			$request = wp_remote_post($this->api_url, $request_string);
			if (!is_wp_error($request) || wp_remote_retrieve_response_code($request) === 200) {
				return $request['body'];
			}
			return false;
		
		}
	}
	
	/** get license key **/
	public function is_verify_license_key($slug='', $key=''){
		
		$slug = (!empty($slug))? $slug: $this->slug;
		$saved_key = (!empty($key) )? $key: $this->get_saved_license_key($slug);
		
		if($saved_key!=false ){		
						
			global $wp_version;
		
			$args = array(
				'slug' => $this->slug,
				'key'=>$saved_key
			);
			$request_string = array(
				'body' => array(
					'action' => 'verify_envato_purchase', 
					'request' => serialize($args),
					'api-key' => md5(get_bloginfo('url'))
				),
				'user-agent' => 'WordPress/' . $wp_version . '; ' . get_bloginfo('url')
			);
			
		
			$request = wp_remote_post($this->api_url, $request_string);
			if (!is_wp_error($request) || wp_remote_retrieve_response_code($request) === 200) {
				$license_check_status =  $request['body'];
				
				return ($license_check_status==1)? true:false;
					
			}			
		}	
	}
	
	public function get_saved_license_key($slug=''){
		$licenses =get_option('_evo_licenses');
		
		$slug = (!empty($slug))? $slug: $this->slug;
		
		if(is_array($licenses)&&count($licenses)>0 && !empty($licenses[$slug]) ){	
			return $licenses[$this->slug]['key'];
		}else{
			return false;
		}
	}
	
	public function save_license_key($slug, $key){
		$licenses =get_option('_evo_licenses');
		
		if(!empty($licenses) && count($licenses)>0 && !empty($licenses[$slug]) && !empty($key) ){
			
			$newarray = array();
			
			$this_license = $licenses[$slug];
			
			foreach($this_license as $field=>$val){
				if($field=='key')
					$val=$key;
				
				if($field =='status')
					$val='active';
					
				$newarray[$field]=$val;
			}
			$new_ar[$slug] = $newarray;
			
			$merged=array_merge($licenses,$new_ar);
			
			
			update_option('_evo_licenses',$merged);
			
			return $newarray;
		}else{
			return false;
		}
		
	}
  
}