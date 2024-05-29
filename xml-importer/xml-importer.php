<?php

/**
 * Plugin Name: XML Importer
 * Description: Import posts from an XML file.
 * Version: 1.0
 * Author: Nui Suriya
*/


class Xml_importer {
  public function __construct() {
    add_action('admin_menu', [$this, 'add_admin_menu']);
    add_action('admin_post_xml_importer', [$this, 'handle_form_submission']);
  }

  public function add_admin_menu() {
    add_menu_page('XML Importer', 'XML Importer', 'manage_options', 'xml-importer', [$this, 'xml_importer_page']);
  }

  public function xml_importer_page() {
    ?>
    <div class="wrap">
        <h1>XML Data Importer</h1>

        <!-- File Import Form -->
        <form method="post" enctype="multipart/form-data">
            <input type="file" name="xml_file" accept=".xml">
            <br><br>
            <input type="submit" name="import" value="Import Data" class="button button-primary">
        </form>
    </div>
    <?php

    // Check if form is submitted
    if (isset($_POST['import']) && !empty($_FILES['xml_file']['tmp_name'])) {
        $xml_file = $_FILES['xml_file']['tmp_name'];
        $this->import_xml_data($xml_file);
    }
  }

  public function handle_form_submission() {
    if (!empty($_FILES['xml_file']['tmp_name'])) {
      $xml_file = $_FILES['xml_file']['tmp_name'];
      $this->import_xml_data($xml_file);
    }
    wp_redirect(admin_url('admin.php?page=xml-importer'));
    exit;
  }

  public function import_xml_data($xml_file) {
    $xml_data = simplexml_load_file($xml_file);

    foreach ($xml_data->channel->item as $item) {
      $author = (string) $item->children('dc', true)->creator;
      $post_status = (string) $item->children('wp', true)->status;
      $post_date = (string) $item->children('wp', true)->post_date;
      $post_excerpt = (string) $item->children('excerpt', true)->encoded;
      $post_title = (string) $item->title;
      $post_content = (string) $item->children('content', true)->encoded;

      $categories = $item->category;
      $categoryArray = [];
      foreach($categories as $category) {
          $categoryArray[] = (string) $category;
      }

      // Check if author exists
      $author_id = username_exists($author);

      // Create author if not exists
      if (!$author_id) {
        $random_password = wp_generate_password($length=12, $include_standard_special_chars=false);
        $author_id = wp_create_user($author, $random_password);
        wp_update_user([
          'ID' => $author_id,
          'role' => 'author',
        ]);
      }

      // Set new post
      $new_post = [
        'post_title'    => $post_title,
        'post_content'  => $post_content,
        'post_status'   => $post_status == "future" ? "future" : "publish",
        'post_author'   => $author_id,
        'post_excerpt'  => $post_excerpt,
        'post_type'     => 'post',
        'post_date'     => $post_date,
        'post_category' => $categoryArray,
      ];

      // Insert post
      $post_id = wp_insert_post($new_post);
      wp_set_object_terms($post_id, $categoryArray, 'category');
    }
  }
}

new Xml_importer();
