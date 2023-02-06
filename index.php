<?php

/*
  Plugin Name: Word Filter XL Plugin
  Description: Replace a list of words.
  Version: 1.0
  Author: David Heckel
  Author URL: https://davidheckel.dev
*/

if( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
 
class WordFilterXL {
  function __construct() {
    add_action('admin_menu', array($this, 'wfMenu'));
    if (get_option('plugin_words_to_filter')) add_filter('the_content', array($this, 'filterLogic')); 
    add_action('admin_init', array($this, 'wfSettings'));
  }

  function wfMenu() {
    $mainPageHook = add_menu_page('Words To Filter', 'Word Filter', 'manage_options', 'wfMenu', array($this, 'wordFilterXL'), 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAiIGhlaWdodD0iMjAiIHZpZXdCb3g9IjAgMCAyMCAyMCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHBhdGggZmlsbC1ydWxlPSJldmVub2RkIiBjbGlwLXJ1bGU9ImV2ZW5vZGQiIGQ9Ik0xMCAyMEMxNS41MjI5IDIwIDIwIDE1LjUyMjkgMjAgMTBDMjAgNC40NzcxNCAxNS41MjI5IDAgMTAgMEM0LjQ3NzE0IDAgMCA0LjQ3NzE0IDAgMTBDMCAxNS41MjI5IDQuNDc3MTQgMjAgMTAgMjBaTTExLjk5IDcuNDQ2NjZMMTAuMDc4MSAxLjU2MjVMOC4xNjYyNiA3LjQ0NjY2SDEuOTc5MjhMNi45ODQ2NSAxMS4wODMzTDUuMDcyNzUgMTYuOTY3NEwxMC4wNzgxIDEzLjMzMDhMMTUuMDgzNSAxNi45Njc0TDEzLjE3MTYgMTEuMDgzM0wxOC4xNzcgNy40NDY2NkgxMS45OVoiIGZpbGw9IiNGRkRGOEQiLz4KPC9zdmc+Cg==', 100);
    add_submenu_page( 'wfMenu', 'Word To Filter', 'Words List', 'manage_options', 'wfMenu', array($this, 'wordFilterXL'));
    add_submenu_page( 'wfMenu', 'Word Filter Options', 'Options', 'manage_options', 'wf-options', array($this, 'wfOptions'));
    add_action("load-{$mainPageHook}", array($this, 'mainPageAssets'));
  }

  function mainPageAssets() {
    wp_enqueue_style('filterAdminCSS', plugin_dir_url(__FILE__) . 'styles.css');
  }
  
  function handleForm () {
    if (wp_verify_nonce( $_POST['ourNonce'], 'saveFilterWords' ) AND current_user_can('manage_options')) {
      update_option('plugin_words_to_filter', sanitize_text_field( $_POST['plugin_wfxl'] )); ?>
      <div class="updated">
        <p>Your filtered words were saved.</p>
      </div>
    <?php } else { ?>
      <div class="error">
        <p>Sorry, you don't have permission to save that.</p>
      </div>
    <?php } 
  }

  function wordFilterXL() { ?>
    <div class="wrap">
      <h1>Word Filter</h1>
      <?php if ($_POST['justsubmitted'] == "true") $this->handleForm() ?>
      <form method="POST">
        <input type="hidden" name="justsubmitted" value="true">
        <?php wp_nonce_field( 'saveFilterWords', 'ourNonce' ) ?>
        <label for="plugin_wfxl"><p>Ender a <strong>comma-seperated</strong> list of words to filter.</p></label>
        <div class="wf__flex-container">
          <textarea name="plugin_wfxl" id="plugin_wfxl" placeholder="bad, horrible, aweful"><?php echo esc_textarea(get_option('plugin_words_to_filter')) ?></textarea>
        </div>
        <input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes">
      </form>
    </div>
  <?php }
  
  function filterLogic($content) {
    $badWords = explode(',', get_option('plugin_words_to_filter'));
    $badWordsTrimmed = array_map('trim', $badWords);
    return str_ireplace($badWordsTrimmed, esc_html(get_option('replacementText'), '****'), $content);
  }

  function wfOptions() { ?>
    <div class="wrap">
      <h1>Word Filter Options</h1>
      <form action="options.php" method="POST">
        <?php 
          settings_errors();
          settings_fields( 'replacementFields' );
          do_settings_sections('wf-options');
          submit_button( );
        ?>
      </form>
    </div>
    <?php }

  function wfSettings() {
    add_settings_section('replacement-text-section', null, null, 'wf-options');
    register_setting( 'replacementFields', 'replacementText' );
    add_settings_field( 'replacement-text', 'Filtered Text', array($this, 'replacementFieldHTML'), 'wf-options', 'replacement-text-section' );
  }

  function replacementFieldHTML() { ?>
  <input type="text" name="replacementText" value="<?php echo esc_attr(get_option('replacementText', '****')) ?>">
  <p class="description">Leave blank to simply remove the filtered words.</p>
  <?php }

}
  
$WordFilterXL = new WordFilterXL();