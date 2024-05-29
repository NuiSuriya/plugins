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

    // Check if the form is submitted
    if (isset($_POST['import']) && !empty($_FILES['xml_file'])) {
      $this->import_xml_data($_FILES['xml_file']);
      $xml_file = $_FILES['xml_file']['tmp_name'];
      $this->import_xml_data($xml_file);
    }
  }
}

new Xml_importer();
