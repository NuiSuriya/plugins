<?php

function add_admin_menu() {
  add_menu_page('Custom REST API', 'Custom REST API', 'manage_options', 'custom-rest-api', 'custom_rest_api_page');
  add_submenu_page('custom-rest-api', 'All published posts', 'All published posts', 'manage_options', 'show-posts', 'show_posts_page');
  add_submenu_page('custom-rest-api', 'Update post status', 'Update post status', 'manage_options', 'update-post-status', 'update_post_status_page');
}

add_action('admin_menu', 'add_admin_menu');

function custom_rest_api_page() {
  ?>
  <div class="wrap">
    <h1>Custom REST API</h1>
    <a href="<?php echo admin_url('admin.php?page=show-posts'); ?>" class="button button-primary">All published posts by an author</a>
    <a href="<?php echo admin_url('admin.php?page=update-post-status'); ?>" class="button button-primary">Update post status</a>
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
      <a href="<?php echo admin_url('admin.php?page=custom-rest-api'); ?>" class="button button-primary" style="margin-left: 5px">Back</a>

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

function update_post_status_page() {
  ?>
  <div class="wrap">
    <h1>Custom REST API</h1>
    <div style="display: flex; align-items: center;">
      <p style="margin-right: 10px;">Update Post Status</p>

      <!-- Show dropdown of all posts -->
      <form id="post-form" method="post">
        <select name="post_id" id="post-id">
          <option value="">Select Post</option>
          <?php
          $posts = get_posts(['post_type' => 'post', 'post_status' => 'any', 'numberposts' => -1]);
          foreach ($posts as $post) {
            echo '<option value="' . $post->ID . '">' . $post->post_title . '</option>';
          }
          ?>
        </select>

        <!-- Show dropdown of all post statuses -->
        <select name="status" id="status">
          <option value="">Select Status</option>
          <option value="publish">Publish</option>
          <option value="pending">Pending</option>
          <option value="draft">Draft</option>
          <option value="auto-draft">Auto Draft</option>
          <option value="future">Future</option>
          <option value="private">Private</option>
          <option value="inherit">Inherit</option>
          <option value="trash">Trash</option>
        </select>
        <input type="submit" value="Update Status" class="button button-primary">
      </form>
      
      <a href="<?php echo admin_url('admin.php?page=custom-rest-api'); ?>" class="button button-primary" style="margin-left: 5px">Back</a>
    </div>

    <!-- Show result message -->
    <div id="result" style="display: none;"></div>

    <!--  -->
    <script>
      jQuery(document).ready(function($) {
        $('#post-form').on('submit', function(e) {
          e.preventDefault();
          var post_id = $('#post-id').val();
          var status = $('#status').val();
          $.post('/wp-json/custom-rest-api/v1/post/' + post_id + '/status/' + status, function(data) {
            $('#result').text('Post status updated successfully').show();
          });
        });
      });
    </script>
  </div>
  <?php
}
