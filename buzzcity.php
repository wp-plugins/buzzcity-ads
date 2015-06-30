<?php
/*
Plugin Name: BuzzCity Ads
Plugin URI: http://www.buzzcity.com
Description: Displays BuzzCity ads on your WordPress blog
Author: BuzzCity Pte Ltd
Version: 1.1
Author URI: http://www.buzzcity.com
*/




/* =================================================================
  Plug-in settings to allow buzzcity ads to show anywhere on pages 
====================================================================*/


add_action('admin_menu', 'bcads_on_page_menu');

define('BCAD_CONTAINER_SHORT_TAG', 'bcads_on_page');
define('BCAD_CONTAINER_ID_PREFIX', 'bop-');

function bcads_on_page_menu() {
    global $bc_plugin_hook;
    $bc_plugin_hook = add_options_page('BuzzCity Ads on Pages Options', 'BuzzCity Ads on Pages', 'manage_options', 'bcads_on_page_options', 'bcads_on_page_plugin_options');
    add_action('admin_init', 'register_bop_settings');
}

add_filter('plugin_action_links', 'bop_plugin_action_links', 10, 2);

function bop_plugin_action_links($links, $file) {
    static $this_plugin;

    if (!$this_plugin) {
        $this_plugin = plugin_basename(__FILE__);
    }

    if ($file == $this_plugin) {
        $settings_link = '<a href="' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=bcads_on_page_options">Settings</a>';
        array_unshift($links, $settings_link);
    }

    return $links;
}



function register_bop_settings() { // whitelist options
  register_setting('bcads_on_page_options', 'bop_options_field');
}


/*===================================
  print the options pages
=================================== */
function bcads_on_page_plugin_options() {
?>
  <div class="wrap">
    <h2>BuzzCity Ads on Pages: Options</h2>
    <form method="post" action="options.php">
    <?php
        wp_nonce_field('update-options'); 
        settings_fields('bcads_on_page_options'); 
        $options = get_option('bop_options_field');
        $num_containers = $options['num_of_bop_containers'];
    ?>
    
        <script language="JavaScript">
        function validate(evt) {
          var theEvent = evt || window.event;
          var key = theEvent.keyCode || theEvent.which;
          if ((key == 8) || (key == 9) || (key == 13)) {
          }
          else {
            key = String.fromCharCode( key );
            var regex = /[0-9]|\./;
            if(!regex.test(key)) {
              theEvent.returnValue = false;
              theEvent.preventDefault();
            }
          }
        }
        </script>

        <table class="form-table">
          <tr valign="top">
            <th scope="row">Number of BuzzCity Ads Container</th>
            <td><input type='number' name="bop_options_field[num_of_bop_containers]" value="<?php echo $num_containers;?>"  onkeypress='validate(event)' /></td>
          </tr>

          <tr>
            <td></td>
            <td>
              <p class="submit">
              <input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
              </p>
            </td>
          </tr>
        </table>
    </form>
  </div>
<?php
}




/* ===============================
  C O R E    C O D E 
================================*/

// Main Function Code, to be included on themes
function bc_widgets_on_template($id) {
    $arr = array('id' => $id);
    echo bc_widgets_on_page_func($arr);
}


function bc_widgets_on_page_func($atts){
    reg_bop_container();
    extract(shortcode_atts(array('id' => '1'), $atts));
    $sidebar_name = BCAD_CONTAINER_ID_PREFIX . $id;
    $str =  "<div id='$sidebar_name'>";
    ob_start();
    if (!function_exists('dynamic_sidebar') || !dynamic_sidebar($sidebar_name)) :
    endif;
    $myStr = ob_get_contents();
    ob_end_clean();
    $str .= $myStr;
    $str .=  "</div>";
    return $str;
}



function reg_bop_container() {
    $options = get_option('bop_options_field');
    $num_containers = $options['num_of_bop_containers'];
    // register the main sidebar
    if (function_exists('register_sidebar')) {
        for ($i = 1; $i <= $num_containers; $i++) {
            if (function_exists('register_sidebar')) {
                $name = "BuzzCity Ads Container $i";
                $id = BCAD_CONTAINER_ID_PREFIX . $i; 
                $desc = "Use shortcode '[" . BCAD_CONTAINER_SHORT_TAG ." id={$i}]'";
                register_sidebar(array(
                    'name' => __($name, 'bop'),
                    'id' => $id ,
                    'description' => __($desc, 'bop'),
                    'before_widget' => '<span id="%1$s">',
                    'after_widget' => '</span>',
                    'before_title' => '<h2 class="widgettitle" style="display:none">',
                    'after_title' => '</h2>',
                ));
            }
        }
    }
}



add_action('admin_init', 'reg_bop_container'); 
add_shortcode(BCAD_CONTAINER_SHORT_TAG, 'bc_widgets_on_page_func');

/* ===============================
  End of Plug-in settings 
================================*/
 
class BuzzcityWidget extends WP_Widget {
// Reference: http://codex.wordpress.org/Widgets_API
    
    private $bannerSizesAvailable = array(
            "320x50" => "320 × 50 (XX-Large)",
            "300x50" => "300 × 50 (X-Large)",
            "216x36" => "216 × 36 (Large)",
            "168x28" => "168 × 28 (Medium)",
            "120x20" => "120 × 20 (Standard)",
            "320x480"=> "320 × 480 (Large Portrait)",
            "300x250"=> "300 × 250 (Rectangle)",
            "250x250"=> "250 × 250 (Square)",
            "468x60" => "468 × 60",
            "728x90" => "728 × 90 (Leaderboard)"
        );
    
    public function __construct() {
        parent::__construct(
            'buzzcity_widget',
            'BuzzCity Ads',
            array( 'description' => 'Displays BuzzCity ads on your WordPress blog' )
        );
    }

    public function widget($args, $instance) {
        extract($args, EXTR_SKIP);
     
        $title = empty($instance['title']) ? ' ' : apply_filters('widget_title', $instance['title']);
        $partnerId = $instance['partnerId'];
        $adsType = $instance['adsType'];
        $bannerSize = $instance['bannerSize'];
        
        echo $before_widget;

        if (!empty($title)) {
          echo $before_title . $title . $after_title;
        }
        
        ?>
        <script type="text/javascript">
            var bcads_vars = {
                partnerid : <?php echo $partnerId; ?>,
                get       : '<?php echo $adsType; ?>',
            <?php if('rich'==$adsType): ?>
                imgsize   : '<?php echo $bannerSize; ?>',
                sync      : 1  
            <?php else: ?>
                imgsize   : '<?php echo $bannerSize; ?>'
            <?php endif; ?>
            };
        </script>
        <script type="text/javascript" src="http://js.buzzcity.net/bcads.js"></script> 
        <?php
     
        echo $after_widget;
    }
  
    public function form($instance) {
        $defaults = array( 'title' => 'Ads by BuzzCity', 'partnerId' => 8816 );
        $instance = wp_parse_args( (array) $instance, $defaults );
        $title = $instance['title'];
        $partnerId = $instance['partnerId'];
        $adsType = $instance['adsType'];
        $bannerSize = $instance['bannerSize'];
        ?>
        <p>
        <label for="<?php echo $this->get_field_id('title'); ?>">Title:</label>
        <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" />
        <label for="<?php echo $this->get_field_id('partnerId'); ?>">Partner ID:</label>
        <input class="widefat" id="<?php echo $this->get_field_id('partnerId'); ?>" name="<?php echo $this->get_field_name('partnerId'); ?>" type="number" value="<?php echo esc_attr($partnerId); ?>" />
        <label for="<?php echo $this->get_field_id('adsType'); ?>">Banner Type:</label>
        <select class="widefat"  id="<?php echo $this->get_field_id('adsType'); ?>" name="<?php echo $this->get_field_name('adsType'); ?>" >
            <option value="rich" <?php echo ('rich' == $adsType) ? 'selected' : '' ?> >Rich Media Banner</option>
            <option value="image" <?php echo ('image' == $adsType) ? 'selected' : '' ?> >Graphical Ad Banner</option>
            <option value="text" <?php echo ('text' == $adsType) ? 'selected' : '' ?> >Text Ad Banner</option>
        </select>
        <label for="<?php echo $this->get_field_id('bannerSize'); ?>">Banner Size:</label>
        <select class="widefat"  id="<?php echo $this->get_field_id('bannerSize'); ?>" name="<?php echo $this->get_field_name('bannerSize'); ?>" >
            <?php foreach ($this->bannerSizesAvailable as $value => $label): ?>
            <option value="<?php echo $value ?>" <?php echo ($value == $bannerSize) ? 'selected' : '' ?>>
                <?php echo $label ?>
            </option>
            <?php endforeach; ?>
            
        </select>
        </p>
        <?php
    }
 
    public function update($new_instance, $old_instance) {
        $instance = array();
        $instance['title'] = filter_var($new_instance['title'], FILTER_SANITIZE_STRING);
        $instance['partnerId'] = filter_var($new_instance['partnerId'], FILTER_VALIDATE_INT);
        $instance['adsType'] = in_array($new_instance['adsType'], array('rich', 'image', 'text')) ? $new_instance['adsType'] : 'rich';
        $instance['bannerSize'] = isset($this->bannerSizesAvailable[$new_instance['bannerSize']]) ? $new_instance['bannerSize'] : '320x50';
        return $instance;
    }
 
}

add_action( 'widgets_init', create_function('', 'return register_widget("BuzzcityWidget");') );?>
