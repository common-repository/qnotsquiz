<?php
/**
 * Plugin Name: qnotsquiz
 * Description: Create quiz with easy navigation.
 * Version: 1.0.0
 * Author: Muniyandi
 * Author URI: http://tbginfotech.com
 * License: GPL2
 */

//if this file accessed directly then exit



if( !defined('ABSPATH')){
    die;
}

//check for ACF installed or not
/*if( !function_exists( 'the_field' ) ) {
  add_action( 'admin_notices', 'qnotsquiz_my_acf_notice' );
}

//if ACF not installed show admin notice
function qnotsquiz_my_acf_notice() {
  ?>
  <div class="update-nag notice">
      <p><?php _e( 'QnotsQuiz plugin requires ACF-Advanced custom fields plugin to run properly.
      Please install ACF plugin.', 'my_plugin_textdomain' ); ?></p>
  </div>
  <?php
}*/

//packaging acf
// 1. customize ACF path
add_filter('acf/settings/path', 'my_acf_settings_path');

function my_acf_settings_path( $path ) {

    // update path
    $path = plugin_dir_path( __FILE__ ) .('/acf/');

    // return
    return $path;

}


// 2. customize ACF dir
add_filter('acf/settings/dir', 'my_acf_settings_dir');

function my_acf_settings_dir( $dir ) {

    // update path
    $dir = plugins_url('/acf/');

    // return
    return $dir;

}


// 3. Hide ACF field group menu item
add_filter('/acf/settings/show_admin', '__return_false');


// 4. Include ACF
include_once( plugin_dir_path( __FILE__ ) .('/acf/acf.php') );


//add our script and stylesheet
function qnotsquiz_add_scripts() {
  wp_register_style( 'qnotsquiz_style',plugins_url('/css/style.css', __FILE__), array(), '1.1', 'all');
  wp_register_script( 'qnotsquiz_script',plugins_url('/js/script.js', __FILE__), array ( 'jquery' ), 1.1, true);
  wp_register_script( 'qnotsquiz_chart_script','https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.2/Chart.min.js', array ( 'jquery' ), 1.1, true);
  wp_enqueue_style('qnotsquiz_style');
  wp_enqueue_script('qnotsquiz_script');
   wp_enqueue_script('qnotsquiz_chart_script');
  //localize data for script
  wp_localize_script( 'qnotsquiz_script', 'POST_SUBMITTER', array(
      'root' => esc_url_raw( rest_url() ),
      'nonce' => wp_create_nonce( 'wp_rest' ),
      'success' => __( 'Thanks for your submission!', 'your-text-domain' ),
      'failure' => __( 'Your submission could not be processed.', 'your-text-domain' ),
      'current_user_id' => get_current_user_id()
    )
  );
}
add_action( 'wp_enqueue_scripts', 'qnotsquiz_add_scripts' );


//add rest support for ACF fields
function qnotsquiz_qnotsquiz_rest(){
  register_rest_field('quiz','questions',array(
    'get_callback' => function(){return get_field('questions');}
    ));
  register_rest_field('questions','question',array(
    'get_callback' => function(){return get_field('question');}
    ));
  register_rest_field('questions','answerchoice1',array(
    'get_callback' => function(){return get_field('answerchoice1');}
    ));
  register_rest_field('questions','answerchoice2',array(
    'get_callback' => function(){return get_field('answerchoice2');}
    ));
  register_rest_field('questions','answerchoice3',array(
    'get_callback' => function(){return get_field('answerchoice3');}
    ));
  register_rest_field('questions','answerchoice4',array(
    'get_callback' => function(){return get_field('answerchoice4');}
    ));
  register_rest_field('questions','correctanswer',array(
    'get_callback' => function(){return get_field('correctanswer');}
    ));
  register_rest_field('qnots_attempts','user_id',array(
    'get_callback' => function(){return get_field('user_id');}
    ));
  register_rest_field('qnots_attempts','user_name',array(
    'get_callback' => function(){return get_field('user_name');}
    ));
  register_rest_field('qnots_attempts','quiz_id',array(
    'get_callback' => function(){return get_field('quiz_id');}
    ));
  register_rest_field('qnots_attempts','attempts_log',array(
    'get_callback' => function(){return get_field('attempts_log');}
    ));
  register_rest_field('qnots_attempts','score',array(
    'get_callback' => function(){return get_field('score');}
    ));
  register_rest_route( 'qnotsAttempts/v1/','/qnots_attempts',array(
    'methods'=>'POST',
    'callback'=>'updateQnotsAttempts'
    ));
  register_rest_route( 'qnotsAttempts/v1/','/qnots_attempts',array(
            'methods'             => WP_REST_SERVER::READABLE,
            'callback'            => 'getQnotsAttempts',

    ));
}

function getQnotsAttempts($data){
  $qnotsAmps=new WP_Query(array(
    'post_type'=>'qnots_attempts',
    'posts_per_page' => -1,
    'nopaging' => true,
    'orderby'=>'meta_value_num',
    'meta_key'=>'score',
    'order'=>'desc'
    ));
  $attempsList=array();
  while($qnotsAmps->have_posts()){
    $qnotsAmps->the_post();
    if($data['quiz_id']==get_field('quiz_id')){
      array_push($attempsList,array(
        'user_name'=>get_field('user_name'),
        'score'=>get_field('score')
        ));
    }
  }
  return $attempsList;
    }

//     flush_rewrite_rules(true); // FIXME: <------- DONT LEAVE ME HERE
// }


add_action('rest_api_init','qnotsquiz_qnotsquiz_rest');

add_action( 'rest_api_init', 'wp_rest_filter_add_filters' );
 /**
  * Add the necessary filter to each post type
  **/
function wp_rest_filter_add_filters() {
    foreach ( get_post_types( array( 'show_in_rest' => true ), 'objects' ) as $post_type ) {
        add_filter( 'rest_' . $post_type->name . '_query', 'wp_rest_filter_add_filter_param', 10, 2 );
    }
}
/**
 * Add the filter parameter
 *
 * @param  array           $args    The query arguments.
 * @param  WP_REST_Request $request Full details about the request.
 * @return array $args.
 **/
function wp_rest_filter_add_filter_param( $args, $request ) {
    // Bail out if no filter parameter is set.
    if ( empty( $request['filter'] ) || ! is_array( $request['filter'] ) ) {
        return $args;
    }
    $filter = $request['filter'];
    if ( isset( $filter['posts_per_page'] ) && ( (int) $filter['posts_per_page'] >= 1 && (int) $filter['posts_per_page'] <= 100 ) ) {
        $args['posts_per_page'] = $filter['posts_per_page'];
    }
    global $wp;
    $vars = apply_filters( 'rest_query_vars', $wp->public_query_vars );
    function allow_meta_query( $valid_vars )
    {
        $valid_vars = array_merge( $valid_vars, array( 'meta_query', 'meta_key', 'meta_value', 'meta_compare' ) );
        return $valid_vars;
    }
    $vars = allow_meta_query( $vars );

    foreach ( $vars as $var ) {
        if ( isset( $filter[ $var ] ) ) {
            $args[ $var ] = $filter[ $var ];
        }
    }
    return $args;
}

//save quiz attempts
function updateQnotsAttempts($data){
  $current_user = wp_get_current_user();
    if ( 0 == $current_user->ID ) {
        $user_name='Guest';
    } else {
        $user_name=$current_user->display_name;

    }
  wp_insert_post(array(

              'post_title'=>'new quizAttempt',
              'post_type'=>'qnots_attempts',


            'post_status' => 'publish',

            meta_input=>array(
              'user_id'=> get_current_user_id(),
              'user_name'=> $user_name,
            'quiz_id'=> $data['quiz_id'],
            'attempts_log'=> $data['attempts_log'],
              'score'=>$data['score']
              )
    ));
}


// overall template of the qnotsquiz
function qnotsquiz_shortcode ($qnotsquizID) {
ob_start();

   if(!get_option( 'qnotsquiz_start_text' ))
    $qnotsquiz_custom_start_text="START";
  else
    $qnotsquiz_custom_start_text=get_option( 'qnotsquiz_start_text' );



 ?>
<div class="quiz" id="quiz">
  <!-- <div class="quizTitle hide"><?php echo get_the_title();?></div>
  <div class="quizLink hide"><?php echo the_permalink();?></div> -->
  <button id="close">close</button>
  <div class="final hide">
    <ul>
                <li  id="finalSectionResult" class=" active">Results</li>
                <li  id="finalSectionSolutions"class="">View Solutions</li>
                <li  id="finalSectionLeaderBoard"class="">LeaderBoard</li>

                </ul>
  </div>

    <label id="qno">Question 1 of totalQuestions</label>

  <div class="questcontent">
    <div class="hide" data-url="<?php echo get_site_url();?>"  id="url"></div>
    <div class="hiddenID" data-quizid="<?php echo $qnotsquizID[0]; ?>" id="quizid"></div>
    <div class="hiddenID" id="questionid">0</div>

<div class="insideQuest">

                <div class="question"></div>
              <div class=" nav option option1"></div>
              <div class=" nav option option2"></div>
              <div class=" nav option option3"></div>
              <div class=" nav option option4"></div>
              <div class="hiddenID" id="questionid">0</div>
</div>

<ul class='questnav'>
<li></li>
<li class="prev"><button class="nav" id="prev">Prev</button></li>
<li class="finish"><button class="nav" id="finish">Finish</button></li>
<li class="next"><button class="nav" id="next">Next</button></li>
</ul>
<div class="navigator showprogress">
 Toggle Navigator
  </div>
<div class="progress hide">
</div>

</div>

</div>
<div id="startQuizBtn"><?php echo $qnotsquiz_custom_start_text; ?></div>

<?php

 $ReturnString = ob_get_contents();
    ob_end_clean();
    return $ReturnString;
}
add_shortcode('qnotsquiz', 'qnotsquiz_shortcode');


//show short codes for each quiz in the admin panel
add_filter( 'manage_quiz_posts_columns', 'qnotsquiz_revealid_add_id_column', 5 );
add_action( 'manage_quiz_posts_custom_column', 'qnotsquiz_revealid_id_column_content', 5, 2 );


function qnotsquiz_revealid_add_id_column( $columns ) {
   $columns['revealid_id'] = 'shortcode';
   return $columns;
}

function qnotsquiz_revealid_id_column_content( $column, $id ) {
  if( 'revealid_id' == $column ) {
    echo "[qnotsquiz $id]";
  }
}

//register custom posttypes quiz and questions
function qnotsquiz_postTypes(){
register_post_type('quiz',array(
  'show_in_rest' => true,
  'show_in_menu' => false,
  'supports'=>array('title','editor'),
  'rewrite' => array('slug'=>'quiz'),
  'has_archive' => true,
  'public' => true,
  'labels' => array(
    'name' => 'quiz'
    ),

  ));

register_post_type('questions',array(
  'show_in_rest' => true,
  'show_in_menu' => false,
  'supports'=>array('title'),
  'rewrite' => array('slug'=>'questions'),
  'has_archive' => true,
  'public' => true,
  'labels' => array(
    'name' => 'questions'
    ),

  ));

register_post_type('qnots_attempts',array(
  'show_in_rest' => true,

  'rest_controller_class' => 'WP_REST_Posts_Controller',
  'show_in_menu' => false,
  'supports'=>array('title'),
  'rewrite' => array('slug'=>'qnots_attempts'),
  'has_archive' => true,
  'public' => true,
  'labels' => array(
    'name' => 'qnots_attempts'
    ),

  ));


//add custom fields
if(function_exists("register_field_group"))
{
  register_field_group(array (
    'id' => 'acf_question',
    'title' => 'question',
    'fields' => array (
      array (
        'key' => 'field_5b62889f3b523',
        'label' => 'question',
        'name' => 'question',
        'type' => 'textarea',
        'required' => 1,
        'default_value' => '',
        'placeholder' => '',
        'maxlength' => '',
        'rows' => '',
        'formatting' => 'br',
      ),
      array (
        'key' => 'field_5b62890f3b524',
        'label' => 'AnswerChoice1',
        'name' => 'answerchoice1',
        'type' => 'text',
        'required' => 1,
        'default_value' => '',
        'placeholder' => '',
        'prepend' => '',
        'append' => '',
        'formatting' => 'html',
        'maxlength' => '',
      ),
      array (
        'key' => 'field_5b6289673b525',
        'label' => 'AnswerChoice2',
        'name' => 'answerchoice2',
        'type' => 'text',
        'required' => 1,
        'default_value' => '',
        'placeholder' => '',
        'prepend' => '',
        'append' => '',
        'formatting' => 'html',
        'maxlength' => '',
      ),
      array (
        'key' => 'field_5b62897e3b526',
        'label' => 'AnswerChoice3',
        'name' => 'answerchoice3',
        'type' => 'text',
        'required' => 1,
        'default_value' => '',
        'placeholder' => '',
        'prepend' => '',
        'append' => '',
        'formatting' => 'html',
        'maxlength' => '',
      ),
      array (
        'key' => 'field_5b6289a63b527',
        'label' => 'AnswerChoice4',
        'name' => 'answerchoice4',
        'type' => 'text',
        'required' => 1,
        'default_value' => '',
        'placeholder' => '',
        'prepend' => '',
        'append' => '',
        'formatting' => 'html',
        'maxlength' => '',
      ),
      array (
        'key' => 'field_5b6289c13b528',
        'label' => 'Select Correct Answer Choice',
        'name' => 'correctanswer',
        'type' => 'select',
        'required' => 1,
        'choices' => array (
          1 => 1,
          2 => 2,
          3 => 3,
          4 => 4,
          '' => '',
        ),
        'default_value' => '',
        'allow_null' => 0,
        'multiple' => 0,
      ),
    ),
    'location' => array (
      array (
        array (
          'param' => 'post_type',
          'operator' => '==',
          'value' => 'questions',
          'order_no' => 0,
          'group_no' => 0,
        ),
      ),
    ),
    'options' => array (
      'position' => 'normal',
      'layout' => 'no_box',
      'hide_on_screen' => array (
      ),
    ),
    'menu_order' => 0,
  ));
  register_field_group(array (
    'id' => 'acf_quiz',
    'title' => 'quiz',
    'fields' => array (
      array (
        'key' => 'field_5b628bb02945b',
        'label' => 'Select Questions',
        'name' => 'questions',
        'type' => 'relationship',
        'required' => 1,
        'return_format' => 'id',
        'post_type' => array (
          0 => 'questions',
        ),
        'taxonomy' => array (
          0 => 'all',
        ),
        'filters' => array (
          0 => 'search',
        ),
        'result_elements' => array (
          0 => 'post_title',
        ),
        'max' => '',
      ),
    ),
    'location' => array (
      array (
        array (
          'param' => 'post_type',
          'operator' => '==',
          'value' => 'quiz',
          'order_no' => 0,
          'group_no' => 0,
        ),
      ),
    ),
    'options' => array (
      'position' => 'normal',
      'layout' => 'no_box',
      'hide_on_screen' => array (
      ),
    ),
    'menu_order' => 0,
  ));
  register_field_group(array (
    'id' => 'acf_qnots_attempts',
    'title' => 'qnots_attempts',
    'fields' => array (
      array (
        'key' => 'field_5b7f9e3407c34',
        'label' => 'user_id',
        'name' => 'user_id',
        'type' => 'text',
        'default_value' => '',
        'placeholder' => '',
        'prepend' => '',
        'append' => '',
        'formatting' => 'html',
        'maxlength' => '',
      ),
      array (
        'key' => 'field_5b838ec788f58',
        'label' => 'user_name',
        'name' => 'user_name',
        'type' => 'text',
        'default_value' => '',
        'placeholder' => '',
        'prepend' => '',
        'append' => '',
        'formatting' => 'html',
        'maxlength' => '',
      ),
      array (
        'key' => 'field_5b7f9ea607c35',
        'label' => 'quiz_id',
        'name' => 'quiz_id',
        'type' => 'text',
        'default_value' => '',
        'placeholder' => '',
        'prepend' => '',
        'append' => '',
        'formatting' => 'html',
        'maxlength' => '',
      ),
      array (
        'key' => 'field_5b7f9f5107c36',
        'label' => 'attempts_log',
        'name' => 'attempts_log',
        'type' => 'text',
        'default_value' => '',
        'placeholder' => '',
        'prepend' => '',
        'append' => '',
        'formatting' => 'html',
        'maxlength' => '',
      ),
      array (
        'key' => 'field_5b7fa03107c37',
        'label' => 'score',
        'name' => 'score',
        'type' => 'number',
        'default_value' => '',
        'placeholder' => '',
        'prepend' => '',
        'append' => '',
        'min' => '',
        'max' => '',
        'step' => '',
      ),
    ),
    'location' => array (
      array (
        array (
          'param' => 'post_type',
          'operator' => '==',
          'value' => 'qnots_attempts',
          'order_no' => 0,
          'group_no' => 0,
        ),
      ),
    ),
    'options' => array (
      'position' => 'normal',
      'layout' => 'no_box',
      'hide_on_screen' => array (
      ),
    ),
    'menu_order' => 0,
  ));
}


}

//while initialisation show custom post types
add_action('init','qnotsquiz_postTypes');

//Add admin menu
add_action( 'admin_menu', 'qnotsquiz_admin_menu' );

function qnotsquiz_admin_menu(){

  $page_title = 'QnotsQuiz';
  $menu_title = 'QnotsQuiz';
  $capability = 'manage_options';
  $menu_slug  = 'qnotsquiz';
  $function   = 'qnotsquiz_settings_page';
  $icon_url   = 'dashicons-editor-help';
  $position   = 99;

  add_menu_page( $page_title,
                 $menu_title,
                 $capability,
                 $menu_slug,
                 $function,
                 $icon_url,
                 $position );

  add_submenu_page('qnotsquiz', 'General', 'General', 'manage_options', 'qnotsquiz');
  add_submenu_page('qnotsquiz', 'Quizzes', 'Quizzes', 'manage_options', 'edit.php?post_type=quiz');
  add_submenu_page('qnotsquiz', 'Questions', 'Questions', 'manage_options', 'edit.php?post_type=questions');

}

if( !function_exists("qnotsquiz_settings_page") )
{
function qnotsquiz_settings_page(){
  if(!get_option( 'qnotsquiz_start_text' ))
    $qnotsquiz_custom_start_text="START";
  else
    $qnotsquiz_custom_start_text=get_option( 'qnotsquiz_start_text' );
?>

  <h1>QnotsQuiz Settings</h1>
  <form method="post" action="options.php">
    <?php settings_fields( 'qnotsquiz-settings' ); ?>
    <?php do_settings_sections( 'qnotsquiz-settings' ); ?>
    <table class="form-table">
      <tr valign="top">
      <th scope="row">Text to appear on start button:</th>
      <td><input type="text" name="qnotsquiz_start_text" value="<?php echo $qnotsquiz_custom_start_text; ?>"/></td>
      </tr>
    </table>
    <?php submit_button(); ?>
  </form>


<?php
}
}
register_setting('qnotsquiz-settings','qnotsquiz_start_text');





?>