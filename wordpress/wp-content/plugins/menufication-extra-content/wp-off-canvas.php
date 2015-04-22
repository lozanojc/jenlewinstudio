<?php
/*
Plugin Name: Wordpress Menufication Extra Content
Plugin URI: http://www.iveo.se
Description: Generates a responsive Off Canvas-element. Dependencies: jQuery.
Version: 1.0
Author: IVEO
Author URI: http://www.iveo.se
License:  © IVEO AB 2013 - All Rights Reserved

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
*/

if(!class_exists('scssc'))
    require_once "scss.inc.php";

/**
*   Define baseclass
*/
class OffCanvas {

    public static $instance = NULL;
    private $plugin_dir;
    private $plugin_url;
    private $plugin_prefix;
    private $plugin_name;

    /**
    *   Constructor
    */
    public function __construct() {
        $this->plugin_prefix = "wp_off_canvas";
        $this->plugin_name = "Menufication Extra Content";
        $this->plugin_dir = plugin_dir_path(__FILE__);
        $this->plugin_url = plugin_dir_url(__FILE__);

        $this->add_actions();
        $this->add_filters();
    }

    /**
     * Singleton
     */
    public static function getInstance() {
        if(!isset(self::$instance)) {
          self::$instance = new OffCanvas();
        }
        return self::$instance;
    }

    function add_actions() {

        // Adds the admin menu
        add_action('admin_menu', array(&$this, 'admin_menu') );

        // Initialize options
        add_action( 'admin_init', array(&$this, 'options_settings_init') );
        add_action( 'after_setup_theme', array(&$this, 'options_init') );

        //Add CSS, JS and meta
        add_action('wp_print_styles', array(&$this, 'add_stylesheets') );
        add_action('wp_enqueue_scripts', array(&$this, 'add_js') );

        //Adds content and css
        add_action('wp_head', array(&$this, 'add_custom_styling') );
        add_action('wp_footer', array(&$this, 'add_multiple_content') );

        // Registers the deactivation hook
        register_deactivation_hook(__FILE__, array(&$this, 'remove_options_on_deactivation') );
    }

    function is_menufication_active() {
        return class_exists('Menufication');
    }

    function add_filters() {

        add_filter('plugin_action_links', array(&$this, 'add_settings_link'), 10, 2 );
    }

    /**
    * Add menufication JS and localize init-variables
    */
    function add_js () {

        if( $this->is_menufication_active() )
            return;

        $options = get_option( $this->plugin_prefix . '_options' );
        $myFile = $this->plugin_url . "js/jquery.offcanvas.min.js";

        // Add base script
        wp_register_script('offcanvas-js', $myFile, array('jquery') );
        wp_enqueue_script( 'offcanvas-js');

        foreach($this->get_extra_settings() as $key=>$value) {
            $menu_options[$key] = $options[$key];
        }

        foreach($this->get_multiple_settings() as $key=>$value) {
            $menu_options[$key] = $options[$key];
        }

        // Check if user is logged in
        $menu_options['is_user_logged_in'] = is_user_logged_in();

        wp_localize_script('offcanvas-js', $this->plugin_prefix, $menu_options );

        // Add settings script
        wp_register_script('offcanvas-js-setup', $this->plugin_url . "js/offcanvas-setup.js", array('offcanvas-js', 'jquery') );
        wp_enqueue_script( 'offcanvas-js-setup');
    }

    /**
    * Add menufication JS and localize init-variables
    */
    function add_admin_js () {
        // Add settings script
        if( function_exists('wp_enqueue_media') )  {
            wp_enqueue_media();
            wp_register_script('offcanvas-js-admin', $this->plugin_url . "js/offcanvas-admin.js", array('jquery') );
        } else {
            wp_enqueue_script('media-upload');
            wp_enqueue_script('thickbox');
            wp_register_script('offcanvas-js-admin', $this->plugin_url . "js/offcanvas-admin.js", array('jquery','media-upload','thickbox') );
        }


        wp_enqueue_script( 'offcanvas-js-admin');
    }

    /**
    * Add multiple area
    */

    function add_multiple_content() {

        if( $this->is_menufication_active() )
            return;

        echo $this->get_multiple_content();
    }

    function get_multiple_content() {

        $options = get_option( $this->plugin_prefix . '_options' );

        ob_start();

        $prefix = $this->is_menufication_active() ? 'wp_menufication' : 'wp_off_canvas';
        $content = $this->replace_shortcodes( $options['multipleContentElement'] );
        ?>
            <div style="display:none" id="<?php echo $prefix; ?>-multiple-content">
                <?php echo $content; ?>
            </div>
            <div id="<?php echo $prefix; ?>-multiple-toggle"><p id="menu-about" class="menu-about">+&nbsp;&nbsp;About</p></div>
        <?php

        return ob_get_clean();
    }

    function replace_shortcodes($content) {
        $pattern = get_shortcode_regex();
        preg_match_all( '/'. $pattern .'/s', $content, $matches );

        if(array_key_exists( 2, $matches )) {
            foreach( $matches[0] as $match ) {
                $shortcode = do_shortcode($match);
                $content = str_replace($match, $shortcode, $content);
            }
        }

        return wpautop( $content );
    }


    /**
    *   Add menufication stylesheet
    */
    function add_stylesheets () {

        if( $this->is_menufication_active() )
            return;

        $myFile = $this->plugin_url . "css/offcanvas.min.css";

        wp_register_style('offcanvas-css', $myFile);
        wp_enqueue_style('offcanvas-css');
    }

    /**
    *   Add menufication admin stylesheet
    */
    function add_admin_stylesheets () {
        $myFile = $this->plugin_url . '/css/admin.css';

        wp_register_style('offcanvas-admin-css', $myFile);
        wp_enqueue_style('offcanvas-admin-css');
        wp_enqueue_style('thickbox');
    }

    function add_custom_styling() {
        $options = get_option( $this->plugin_prefix . '_options' );
        $scss = new scssc();

        try {
            $style = $scss->compile($options['multipleContentStyle']);
        } catch( Exception $e ) {
            $style = $options['multipleContentStyle'];
        }


        echo '<style>';
            echo $style;
        echo '</style>';
    }


    function admin_menu() {
        $page = add_options_page(
            $this->plugin_name,
            $this->plugin_name,
            'manage_options',
            $this->plugin_prefix,
            array($this, 'settings_page')
        );

        add_action( 'admin_print_styles-' . $page, array(&$this, 'add_admin_stylesheets') );
        add_action( 'admin_print_scripts-' . $page, array(&$this, 'add_admin_js') );
    }


    //** OPTIONS_HANDLING **//
    function options_settings_init() {
        register_setting( $this->plugin_prefix . '_options', $this->plugin_prefix . '_options', array(&$this, 'options_validate') );


        if( !$this->is_menufication_active() ) {
            // Settings section with settings field
            add_settings_section($this->plugin_prefix . '_extra', 'Basic settings', array(&$this, 'menu_extra_fields'), $this->plugin_prefix . '_section');
        }

        // Settings section with multiple off-canvas field
        add_settings_section($this->plugin_prefix . '_extra_multiple', '', array(&$this, 'menu_multiple_fields'), $this->plugin_prefix . '_section');

    }


    // Initalize default options on plugin activation
    function options_init() {
        $options = get_option( $this->plugin_prefix . '_options' );

        // Are our options saved in the DB?
        if ( $options === false ) {
            // If not, we'll save our default options
            $default_options = $this->get_default_options();
            add_option( $this->plugin_prefix . '_options', $default_options);
        }
    }


    //** FUNCTIONS FOR DISPLAYING THE SETTINGS FIELDS **//

    function settings_page() { ?>
        <div class="wrap">

            <div id="icon-themes" class="icon32"><br /></div>

            <h2>Settings for Menufication Extra Content</h2>
            <p>Menufication Extra Content is a plugin which automatically generates an Off-Canvas Content Area where you can place anything you like. With the editor below you can insert text, images, shortcodes etc.. </p>
            <p><b>This plugin works best in combination with <a href="http://codecanyon.net/item/wordpress-menufication/4738650" target="_blank">Menufication</a></b>, which lets you automatically generate a repsonsive menu on the opposite side of the content created by Menufication Extra Content.</p>
            <?php if( class_exists('Menufication') ) { ?>
                <p><b> It looks like you already have Menufication installed, <a href="options-general.php?page=wp_menufication">head over here to find more settings.</a></b></p>
            <?php } ?>
            <!-- If we have any error by submiting the form, they will appear here -->
            <?php // settings_errors( 'settings-errors' ); ?>

            <form id="form-menufication-options" action="options.php" method="post" enctype="multipart/form-data">

                <?php
                    settings_fields($this->plugin_prefix . '_options');
                    do_settings_sections($this->plugin_prefix . '_section');
                ?>
                <p class="submit">
                    <input name="<?php echo $this->plugin_prefix . '_options';  ?>[submit]" id="submit_options_form" type="submit" class="button-primary" value="Save" />
                </p>

            </form>

        </div>
        <?
    }

    function settings_info_text() {
        ?>
        <p>It is recommended to use the viewport meta-tag when using a form inside the content area to prevent a user from zoomig, put the following inside your head-tags:</p>
        <xmp>
            <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no;">
        </xmp>

        <?php
    }


    function menu_extra_fields() {
        $options = get_option( $this->plugin_prefix . '_options' );

        echo "<table class='form-table menufication-table'>";

        // Show all available to the menus
        foreach($this->get_extra_settings() as $key=>$value) {
            // Check whether or not to check the field and add a value
            $checked = ( $options[$key] && $value['type'] == 'checkbox' ) ? 'checked' : '';
            $field_value = $value['type'] == 'text' || $value['type'] == 'hidden' ? 'value="' . $options[$key] . '"' : '';

            ?>
                <tr class="menufication-table-tr">
                    <th class="menufication-table-th"><label for="<?php echo $key; ?>"><?php echo $value['explanation']; ?></label></th>
                <td class="menufication-table-td">
            <?php
            switch( $value['type'] ) {
                case 'select':
                    ?>
                        <select name="<?php echo $this->plugin_prefix . '_options[' . $key . ']'; ?>">
                            <?php foreach($value['value'] as $val) {
                                $selected = ( $options[$key] == $val ) ? 'selected' : '';
                            ?>
                                <option value="<?php echo $val ?>" <?php echo $selected ?> > <?php echo $val ?> </option>
                            <?php } ?>
                        </select>
                    <?php
                break;

                case 'hidden':
                    ?>
                    <input class="image_input" name="<?php echo $this->plugin_prefix . '_options[' . $key . ']'; ?>" type="<?php echo $value['type']; ?>" <?php echo $checked; ?> id="<?php echo $key; ?>" <?php echo $field_value; ?> />
                    <img src="<?php echo $options[$key] ?>" id="<?php echo $key; ?>_thumb" class="image_holder" />
                    <input type="button" class="button-primary upload_image" value="Upload image" id="upload_<?php echo $key; ?>">
                    <input type="button" class="button-secondary remove_image" value="Delete" id="delete_<?php echo $key; ?>">
           <?php break;

            case 'wp_editor':

                    wp_editor( $options[$key], $this->plugin_prefix . '_options[' . $key . ']' );
            break;

            default: ?>
                <input name="<?php echo $this->plugin_prefix . '_options[' . $key . ']'; ?>"
                 type="<?php echo $value['type']; ?>" <?php echo $checked; ?> id="<?php echo $key; ?>" <?php echo $field_value; ?> />
            <?php break;
            }
            ?>
                </td>
            </tr>
            <?php
        }
        echo "</table>";
    }

    function menu_multiple_fields() {
        $options = get_option( $this->plugin_prefix . '_options' );

        echo "<table class='form-table menufication-table multiple-table'>";

        // Show all available to the menus
        foreach($this->get_multiple_settings() as $key=>$value) {
            // Check whether or not to check the field and add a value
            $checked = ( $options[$key] && $value['type'] == 'checkbox' ) ? 'checked' : '';
            $field_value = $value['type'] == 'text' || $value['type'] == 'hidden' ? 'value="' . $options[$key] . '"' : '';

            ?>
                <tr class="menufication-table-tr">
                    <th class="menufication-table-th"><label for="<?php echo $key; ?>"><?php echo $value['explanation']; ?></label></th>
                <td class="menufication-table-td">
            <?php
            switch( $value['type'] ) {
                case 'select':
                    ?>
                        <select name="<?php echo $this->plugin_prefix . '_options[' . $key . ']'; ?>">
                            <?php foreach($value['value'] as $val) {
                                $selected = ( $options[$key] == $val ) ? 'selected' : '';
                            ?>
                                <option value="<?php echo $val ?>" <?php echo $selected ?> > <?php echo $val ?> </option>
                            <?php } ?>
                        </select>
                    <?php
                break;

                case 'hidden':
                    $theme = $this->is_menufication_active() ? Menufication::getInstance()->get_menufication_option('theme') : $options['theme'];
                    $src = strlen($options[$key]) == 0 ? $this->plugin_url . '/img/more-' . $theme . '.png' : $options[$key];
                    $val = 'value="'. $src .'"';
                    ?>
                    <input class="image_input" name="<?php echo $this->plugin_prefix . '_options[' . $key . ']'; ?>" type="<?php echo $value['type']; ?>" <?php echo $checked; ?> id="<?php echo $key; ?>" <?php echo $val; ?> />
                    <img style="max-height:50px; width:auto" src="<?php echo $src; ?>" id="<?php echo $key; ?>_thumb" class="image_holder" />
                    <input type="button" class="button-primary upload_image" value="Upload image" id="upload_<?php echo $key; ?>">
                    <input type="button" class="button-secondary remove_image" value="Delete" id="delete_<?php echo $key; ?>">
           <?php break;

                case 'wp_editor':
                        wp_editor( $options[$key], $this->plugin_prefix . '_options[' . $key . ']' );
                break;

                case 'textarea':
                    ?>
                        <textarea rows="15" cols="60" name="<?php echo $this->plugin_prefix . '_options[' . $key . ']'; ?>"><?php echo $options[$key]; ?></textarea>
                    <?php
                break;

                default: ?>
                    <input name="<?php echo $this->plugin_prefix . '_options[' . $key . ']'; ?>"
                     type="<?php echo $value['type']; ?>" <?php echo $checked; ?> id="<?php echo $key; ?>" <?php echo $field_value; ?> />
                <?php break;
            }
            ?>
                </td>
            </tr>
            <?php
        }
        echo "</table>";
    }



    //** GET SETTINGS **//
    function get_extra_settings() {

        $settings = array(

            'triggerWidth' =>
                array(
                    'type'=> 'text',
                    'explanation' => 'Only create the menu off-canvas content the browser width is less than a certain value (default 770px) <b>Note: only numeric values. "px" is not needed</b>.
                    Leave blank to always generate the menu.',
                    'value' => 'null'
                ),

            'onlyMobile' =>
                array(
                    'type'=> 'checkbox',
                    'explanation' => 'Only create the off-canvas content for mobile devices?',
                    'value' => 'null'
                ),

            'direction' =>
                array(
                    'type'=> 'select',
                    'explanation' => 'Slide in content from: ',
                    'value' => array('left', 'right')
                ),

            'theme' =>
                array(
                    'type'=> 'select',
                    'explanation' => 'Theme color: ',
                    'value' => array('dark', 'light')
                )

        );

        return $settings;
    }

    function get_multiple_settings() {

        $settings = array(
            'enable_wp_off_canvas' =>
                array(
                    'type'=> 'checkbox',
                    'explanation' => 'Enable Menufication Extra Content?',
                    'value' => 'null'
                ),
            'multipleToggleElement' =>
                array(
                    'type'=> 'hidden',
                    'explanation' => 'Icon for toggling the off-canvas content',
                    'value' => ''
                ),

            'multipleContentElement' =>
                array(
                    'type'=> 'wp_editor',
                    'explanation' => 'Content inside the area (the area is 270px wide): ',
                    'value' => 'null'
                ),

            'multipleContentStyle' =>
                array(
                    'type'=> 'textarea',
                    'explanation' => 'Custom styling for the area. <br/><b>Tip:</b> You may use the SCSS-syntax here. It will compile on the fly. ',
                    'value' => ''
                )

        );

        return $settings;
    }


    // Set the default options here
    function get_default_options() {
        $options = array(
            'enable_wp_off_canvas'  =>  true,
            'triggerWidth' =>           770,
            'enableSwipe' =>            true,
            'onlyMobile' =>             false,
            'direction'         =>      'left',
            'theme'             =>      'dark',
            'direction'         =>       'left',
            'multipleContentElement' => '',
            'multipleToggleElement' =>  '',
            'multipleContentStyle' =>   '#menufication-multiple-container, #menufication-non-css3-multiple-container {

            }'
        );

        return $options;
    }


    function options_validate($input) {
        // No validation required
        return $input;
    }

    // Add settings link on plugin page
    function add_settings_link($links, $file) {

        $this_plugin = plugin_basename(dirname(__FILE__) . '/wp-off-canvas.php');

        if ($file == $this_plugin) {
            $settings_link = '<a href="options-general.php?page='. $this->plugin_prefix .'">Settings</a>';
            array_unshift($links, $settings_link);
        }
        return $links;
    }

    // Remove options on deactivation
    function remove_options_on_deactivation() {
        delete_option( $this->plugin_prefix . '_options' );
    }

    function is_off_canvas_active() {
        $options = get_option( $this->plugin_prefix . '_options' );
        return ($options['enable_wp_off_canvas'] === 'on');
    }

}

$offCanvas = OffCanvas::getInstance();


?>