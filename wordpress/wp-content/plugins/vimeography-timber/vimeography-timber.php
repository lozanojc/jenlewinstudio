<?php
/*
Plugin Name: Vimeography Theme: Timber
Plugin URI: http://www.vimeography.com/
Theme Name: Timber
Theme URI: http://vimeography.com/themes/timber
Version: 1.2
Description: is a minimalist portfolio layout that lets your videos do the talking.
Author: Dave Kiss
Author URI:
Copyright: 2014 Dave Kiss
*/

if ( ! class_exists('Vimeography_Themes_Timber') ) {
  class Vimeography_Themes_Timber {

    /**
     * The current version of this Vimeography theme.
     *
     * Make sure to specify it here as well as above
     * in the header metadata and in the readme.txt
     * file, located in the root of the plugin directory.
     *
     * @var string
     */
    public $version = '1.2';

    /**
     * The constructor is used to load the plugin
     * when the Wordpress `plugins_loaded` hook is fired.
     *
     * This calls the `load_theme()` function below.
     */
    public function __construct() {
      add_action( 'plugins_loaded', array($this, 'load_theme') );
    }

    /**
     * The __set magic function creates a variable
     * for anything passed to the Vimeography theme and
     * makes that variable accessible in the mustache template.
     *
     * @param string $name  The key to assign to the property.
     * @param mixed  $value The value of the property.
     */
    public function __set($name, $value) {
      $this->$name = $value;
    }

    /**
     * Reports in to the Vimeography plugin to
     * declare that this theme plugin is installed and activated.
     *
     * Has to be public so that the Wordpress `plugins_loaded`
     * action can reach it.
     */
    public function load_theme() {
      do_action('vimeography/load-theme', __FILE__);
    }

    /**
     * Loads any CSS and Javascript dependencies that this
     * Vimeography theme plugin relies on. This is called
     * from the Vimeography_Renderer class.
     *
     * Uses the standard `wp_register_script()` and
     * `wp_enqueue_script()` functions to load the proper scripts.
     *
     * For scripts and styles that are pre-installed with the
     * Vimeography plugin, use `VIMEOGRAPHY_ASSETS_URL` as the
     * root location, then add the relative path to the JS or CSS file.
     *
     *   wp_register_script('vimeography-utilities', VIMEOGRAPHY_ASSETS_URL.'js/utilities.js', array('jquery'));
     *
     * For scripts and styles that are included in this Vimeography
     * theme plugin's `assets` folder, define the root and relative path
     *
     *   wp_register_style('blueprint', plugins_url('assets/css/blueprint.css', __FILE__));
     *
     */
    public static function load_scripts() {
      // Deregister common scripts and styles here
      wp_deregister_style('fancybox');
      wp_deregister_script('fancybox');
      wp_deregister_script('fitvids');

      // Dequeue common scripts and styles here
      wp_dequeue_style('fancybox');
      wp_dequeue_script('fancybox');
      wp_dequeue_script('fitvids');

      // Register our shared Vimeography javascripts
      wp_register_script('mosaicflow', plugins_url('assets/js/jquery.mosaicflow.min.js', __FILE__), array('jquery') );
      wp_register_script('fancybox', '//cdnjs.cloudflare.com/ajax/libs/fancybox/2.1.5/jquery.fancybox.pack.js', array('jquery'));
      wp_register_script('fitvids', VIMEOGRAPHY_ASSETS_URL.'js/plugins/jquery.fitvids.js', array('jquery'));
      wp_register_script('spin', VIMEOGRAPHY_ASSETS_URL.'js/plugins/spin.min.js', array('jquery'));
      wp_register_script('jquery-spin', VIMEOGRAPHY_ASSETS_URL.'js/plugins/jquery.spin.js', array('jquery', 'spin'));
      wp_register_script('vimeography-utilities', VIMEOGRAPHY_ASSETS_URL.'js/utilities.js', array('jquery'));
      wp_register_script('vimeography-pagination', VIMEOGRAPHY_ASSETS_URL.'js/pagination.js', array('jquery'));
      wp_register_script('vimeography-froogaloop', VIMEOGRAPHY_ASSETS_URL.'js/plugins/froogaloop2.min.js', array('jquery', 'vimeography-utilities'));

      // Register our shared Vimeography stylesheets
      wp_register_style('fancybox', '//cdnjs.cloudflare.com/ajax/libs/fancybox/2.1.5/jquery.fancybox.min.css');
      wp_register_style('vimeography-common', VIMEOGRAPHY_ASSETS_URL.'css/vimeography-common.css');

      // Register our Vimeography theme-specific stylesheets
      wp_register_style('timber', plugins_url('assets/css/timber.css', __FILE__));

      // Register any of your Vimeography theme-specific javascripts here
      wp_register_script('vimeography-timber', plugins_url('assets/js/vimeography-timber.js', __FILE__), array('jquery', 'vimeography-utilities', 'vimeography-pagination') );


      // Enqueue all of our assets
      wp_enqueue_script('mosaicflow');
      wp_enqueue_style('fancybox');
      wp_enqueue_script('fancybox');
      wp_enqueue_style('vimeography-common');
      wp_enqueue_style('timber');
      wp_enqueue_script('fitvids');
      wp_enqueue_script('spin');
      wp_enqueue_script('jquery-spin');
      wp_enqueue_script('vimeography-utilities');
      wp_enqueue_script('vimeography-pagination');
      wp_enqueue_script('vimeography-froogaloop');
      wp_enqueue_script('vimeography-timber');
    }

    /**
     * The last chance to format the Vimeo data
     * before it is sent to the template.
     *
     * All of the available Vimeo data is accessible in the
     * `$this->data` property. You should most likely apply
     * the common Vimeography formatting to the data before you
     * use it.
     *
     *   $helpers->apply_common_formatting($this->data);
     *
     * @return array Vimeo video data.
     */
    public function videos() {
      // optional helpers
      require_once VIMEOGRAPHY_PATH . 'lib/helpers.php';
      $helpers = new Vimeography_Helpers;

      $items = array();

      foreach ($this->data as $video) {
        $video->short_description = $helpers->truncate($video->description, 80);
        $video->created_time = date('F j, Y', strtotime( $video->created_time ) );
        $items[] = $video;
      }

      $items = $helpers->apply_common_formatting($items);
      return $items;
    }

    /**
     * If your Vimeography theme is built to support
     * pagination, it's probably a good idea to make the
     * total pages of Vimeo videos available to the template.
     *
     * @return int Number of pages of video data
     */
    public function total_pages() {
      return ceil($this->total / $this->per_page);
    }

  }

  // Instantiate the plugin at the bottom of the class.
  new Vimeography_Themes_Timber;
}
