<?php

/*
 * Plugin Name: catapi
 * Plugin URI: http://thecatapi.com
 * Description: Retrieving cat images from thecatapi.com
 * Version: 1.0
 * Author: Kevin Diep
 *
 */

//register the hook for the pugin and the widget

add_action ( 'admin_menu', 'thecatapi' );
add_action('widgets_init', create_function('', 'return register_widget("CatSideBar");'));

function thecatapi(){
   add_menu_page ( 'CatAPI', 'CatAPI Settings', 'manage_options', 'catapi-setting', 'catapi_setting', '', '63' );
}

//create the form for the user to input the search result value

function catapi_setting(){
?>
    <h3 id="respond">Set Result Count</h3>
    <?php settings_errors(); ?>
    <form  action="<?php $_SERVER['REQUEST_URI'] ?>" method="post" id="set_result">
        <p>
            <label for="result">
                <small>Set Result Count</small>
            </label>
            <input name="result" id="result" value="" size="22" tabindex="3" type="text">

        </p>
        <p>
            <input name="submit" id="submit" tabindex="5" value="Submit" type="submit">
        </p>
    </form>


<?php
/*
 * Getting the value from the from and got a API call to the cat api to return a list of cats images
 */
    if(isset($_POST['submit'])){
        global $wpdb;
        $search_value=$_POST['result'];
        $response = wp_remote_get('http://thecatapi.com/api/images/get?format=xml&results_per_page='.$search_value);
        $body  = wp_remote_retrieve_body($response);
        $xml  = simplexml_load_string($body);
        $codes = $xml->data->images->image;
        $html_string="<ul id='cats'>";
        $counter=1;
        foreach ($codes as $c){
            $html_string .="<li><img src='".$c->url."' height='500px' width='500px' id='$counter' onclick='replace_cat(this.id)'/></li>";
            $counter++;
        }
        $html_string.="</ul>";


        //saving the cats images into the db as an unorder list

        $table_name = $wpdb->prefix . "catapi";
        $addData = $wpdb->insert($table_name, array(
            'search_result'=>$search_value,
            'html_string'=>$html_string

        ));
    ?>
    <div class="updated notice">
            <p><?php _e( 'Successfully added' ); ?></p>
    </div>
    <?php
    }
}

add_action( 'admin_notices', 'my_updated_notice' );

//creating a class for the widget

class CatSideBar extends WP_Widget {
private $string;
    function CatSideBar(){
        parent::WP_Widget(false, $name = __('Cat API Side Bar Widget', 'wp_widget_plugin') );
    }
    function form($instance) {

// Check values
        $defaults = array(
            'title' => 'RECOMMENDED READING',
            'description' => '<p>Add image URLs here</p>'
        );
        $instance = wp_parse_args( (array) $instance, $defaults );

        $title = esc_attr($instance['title']);

        ?>
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>">
                <i><strong><?php _e( 'Title' ); ?></strong></i><br>
                <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
            </label>
        </p>
        <!-- insert delete here->

        <?php

    }

    function update($new_instance, $old_instance) {
        $instance = $old_instance;
        $instance['title'] = strip_tags($new_instance['title']);
        if ( isset($new_instance['description']) ) {
            if ( current_user_can('unfiltered_html') ) {
                $instance['description'] = $new_instance['description'];

            } else {
                $instance['description'] = wp_filter_post_kses($new_instance['description']);
            }
        }

        return $instance;
    }
    function widget($args, $instance) {
        extract( $args );

// these are the widget options
        $title = apply_filters('widget_title', $instance['title']);
        //$description = $instance['description'];
        echo $before_widget;

// Display the widget
        echo '<div class="widget-text wp_widget_plugin_box" style="width:400px; padding:5px 9px 20px 5px; border: 1px solid rgb(231, 15, 52); background: pink; border-radius: 5px; margin: 10px 0 25px 0;">';
        echo '<div class="widget-title" style="width: 90%; height:30px; margin-left:3%; ">';

// Check if title is set
        if ( $title ) {
            echo $before_title . $title . $after_title ;
        }
        echo '</div>';

// Check if textarea is set
        global $wpdb;
        $description=$wpdb->get_results("select `html_string` from wp_catapi order by id DESC limit 1");
        echo '<div class="widget-textarea" style="width: 90%; margin-left:3%; padding:8px; background-color: white; border-radius: 3px; min-height: 70px;">';
        echo '<p class="wp_widget_plugin_textarea" style="font-size:15px;">'.$description[0]->html_string.'</p>';
        echo '</div>';
        echo '</div>';
        echo $after_widget;
    }

}

//add my JS script to handle the AJAX call to replace the image

function my_scripts()
{
    wp_enqueue_script('cats-script', plugin_dir_url(__FILE__) . 'js/replaceCat.js', array('jquery'), 1.1, true);
}
add_action('wp_enqueue_scripts','my_scripts');

//creating the DB
function catapi(){
    global $wpdb;
    $table_name=$wpdb->prefix."catapi";
    $charset_collate = $wpdb->get_charset_collate ();

    $sql = "CREATE TABLE $table_name (
	id mediumint(9) NOT NULL AUTO_INCREMENT,
	search_result mediumint(9) NOT NULL,	
	html_string text NOT NULL,
	UNIQUE KEY id (id)
	) $charset_collate;";

    require_once (ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta ( $sql );
}
register_activation_hook ( __FILE__, 'catapi' );





?>


