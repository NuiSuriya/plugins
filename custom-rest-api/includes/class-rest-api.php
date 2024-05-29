<?php

class Rest_api {
  // Constructor
  public function __construct() {
    add_action('rest_api_init', [$this, 'register_routes']);
  }

  // Register API Routes
  public function register_routes() {

    // Get all posts of an author
    register_rest_route('custom-rest-api/v1', '/author/(?P<id>\d+)/posts', [
      'methods'   => 'GET',
      'callback'  => [$this, 'get_author_published_posts'],
    ]);

    // Update post status
    register_rest_route('custom-rest-api/v1', '/post/(?P<id>\d+)/status/(?P<status>\w+)', [
      'methods' => 'POST',
      'callback' => [$this, 'update_post_status'],
      'permission_callback' => function () {
        return true; // Change this to set authentication later
      },
    ]);
  }



  public function get_author_published_posts($data) {
    $author_id = $data['id'];
    $args = [
      'author'          => $author_id,
      'post_type'       => 'post',
      'post_status'     => 'publish',
      'posts_per_page'  => -1, // Get all posts
    ];

    $posts = get_posts($args);

    $data = [];

    foreach ($posts as $post) {
      $data[] = [
        'id'      => $post->ID,
        'title'   => $post->post_title,
        'excerpt' => $post->post_excerpt,
        'date'    => $post->post_date,
      ];
    }
    return rest_ensure_response($data);
  }


  public function update_post_status($data) {
    $post_id = $data['id'];
    $new_status = $data['status'];

    // Check if the new status is a valid post status
    if (!in_array($new_status, ['publish', 'pending', 'draft', 'auto-draft', 'future', 'private', 'inherit', 'trash'], true)) {
      return new WP_Error('invalid_status', 'Invalid post status', ['status' => 400]);
    }

    // Check if the post exists
    $post = get_post($post_id);
    if (!$post) {
      return new WP_Error('invalid_post', 'Post does not exist', ['status' => 400]);
    }

    // Check if the new status is the same as the current status
    if ($post->post_status === $new_status) {
      return new WP_Error('same_status', 'Post is already in this status', ['status' => 400]);
    }

    $updated = wp_update_post([
      'ID' => $post_id,
      'post_status' => $new_status,
    ], true);

    if (is_wp_error($updated)) {
      // There was an error in the post update
      return $updated;
    }

    // Check if the post status was actually updated
    $post_after_update = get_post($post_id);
    if ($post_after_update->post_status !== $new_status) {
      return new WP_Error('update_failed', 'Post status update failed', ['status' => 500]);
    }

    return rest_ensure_response([
      'message' => 'Post status updated successfully',
      'post_id' => $post_id,
      'post_title' => $post_after_update->post_title,
      'post_status' => $post_after_update->post_status
    ]);
}
}

new Rest_api();
