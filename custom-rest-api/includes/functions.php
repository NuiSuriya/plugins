<?php

function add_admin_menu() {
  add_menu_page('Custom REST API', 'Custom REST API', 'manage_options', 'custom-rest-api', 'custom_rest_api_page');
}

add_action('admin_menu', 'add_admin_menu');

function custom_rest_api_page() {
  
}
