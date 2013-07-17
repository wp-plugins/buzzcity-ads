<?php
/*
Plugin Name: BuzzCity Ads
Plugin URI: http://www.buzzcity.com
Description: Displays BuzzCity ads on your WordPress blog
Author: BuzzCity Pte Ltd
Version: 1.0
Author URI: http://www.buzzcity.com
*/
 
 
class BuzzcityWidget extends WP_Widget {
// Reference: http://codex.wordpress.org/Widgets_API
    
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
        
        echo $before_widget;

        if (!empty($title)) {
          echo $before_title . $title . $after_title;
        }
        
        ?>
        <script type="text/javascript">
            var bcads_vars = {
                    partnerid : <?php echo $partnerId; ?>,
                    get       : '<?php echo $adsType; ?>'
            };
        </script>
        <script type="text/javascript" src="http://js.buzzcity.net/bcads.js"></script> 
        <?php
     
        echo $after_widget;
    }
  
    public function form($instance) {
        $instance = wp_parse_args( (array) $instance, array( 'title' => 'Ads by BuzzCity' ) );
        $title = $instance['title'];
        $partnerId = $instance['partnerId'];
        $adsType = $instance['adsType'];
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
        </p>
        <?php
    }
 
    public function update($new_instance, $old_instance) {
        $instance = array();
        $instance['title'] = filter_var($new_instance['title'], FILTER_SANITIZE_STRING);
        $instance['partnerId'] = filter_var($new_instance['partnerId'], FILTER_VALIDATE_INT);
        $instance['adsType'] = in_array($new_instance['adsType'], array('rich', 'image', 'text')) ? $new_instance['adsType'] : 'rich';
        return $instance;
    }
 
}

add_action( 'widgets_init', create_function('', 'return register_widget("BuzzcityWidget");') );?>