<?php

function add_admin_menu() {
  add_menu_page('Custom REST API', 'Custom REST API', 'manage_options', 'custom-rest-api', 'custom_rest_api_page');
  add_submenu_page('custom-rest-api', 'All published posts', 'All published posts', 'manage_options', 'show-posts', 'show_posts_page');
}

add_action('admin_menu', 'add_admin_menu');

function custom_rest_api_page() {
  ?>
  <div class="wrap">
    <h1>Custom REST API</h1>
    <a href="<?php echo admin_url('admin.php?page=show-posts'); ?>" class="button button-primary">All published posts by an author</a>
  </div>
  <?php
}

function show_posts_page() {
  ?>
  <div class="wrap">
    <h1>Custom REST API</h1>
    <div style="display: flex; align-items: center;">
      <p style="margin-right: 10px;">See All Published Posts of</p>
      <!-- Show dropdown of all authors -->
      <form id="author-form" method="get">
        <select name="author_id" id="author-id">
          <option value="">Select Author</option>
          <?php
          $authors = get_users(['role' => 'author']);
          foreach ($authors as $author) {
            echo '<option value="' . $author->ID . '">' . $author->display_name . '</option>';
          }
          ?>
        </select>
        <input type="submit" value="Show Posts" class="button button-primary">
      </form>
    </div>
    <table id="results-table" class="wp-list-table widefat fixed striped" style="display: none;">
      <thead>
        <tr>
          <th class="manage-column column-title column-primary">Title</th>
          <th class="manage-column">Excerpt</th>
          <th class="manage-column">Date</th>
        </tr>
      </thead>
      <tbody></tbody>
    </table>
    <script>
      jQuery(document).ready(function($) {
        $('#author-form').on('submit', function(e) {
          e.preventDefault();
          var authorId = $('#author-id').val();
          $.get('/wp-json/custom-rest-api/v1/author/' + authorId + '/posts', function(data) {
            var tbody = $('#results-table tbody');
            tbody.empty();
            $.each(data, function(i, post) {
              var row = $('<tr>');
              row.append($('<td class="column-title column-primary">').text(post.title));
              row.append($('<td>').text(post.excerpt));
              row.append($('<td>').text(post.date));
              tbody.append(row);
            });
            $('#results-table').show();
          });
        });
      });
    </script>
  <?php
}
