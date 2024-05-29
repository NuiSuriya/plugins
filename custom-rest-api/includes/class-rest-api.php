<?php
require_once(ABSPATH . 'wp-admin/includes/user.php'); // Include user.php for wp_delete_user function

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

    // Remove an author and reassign posts to a new author
    // Can pass one of mutiple new author IDs separated by comma
    register_rest_route('custom-rest-api/v1', '/author/(?P<id>\d+)/reassign/(?P<new_ids>[^/]+)', [
      'methods' => 'POST',
      'callback' => [$this, 'remove_author_and_reassign_posts'],
      'permission_callback' => function () {
        return true; // Change this to set authentication later
      },
    ]);

    // Create a new post for an author
    register_rest_route('custom-rest-api/v1', 'author/(?P<id>\d+)/post', [
      'methods' => 'POST',
      'callback' => [$this, 'create_new_post'],
      'args' => [
        'id' => [
          'required' => true,
          'validate_callback' => function ($param) {
            return is_numeric($param);
          },
        ],
      ],
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
      'message'     => 'Post status updated successfully',
      'post_id'     => $post_id,
      'post_title'  => $post_after_update->post_title,
      'post_status' => $post_after_update->post_status
    ]);
  }

  public function remove_author_and_reassign_posts($data) {
    $author_id = $data['id'];
    $new_author_ids = explode(',', $data['new_ids']);

    // Check if the author exists
    $author = get_user_by('id', $author_id);
    if (!$author) {
      return new WP_Error('invalid_author', 'Author does not exist', ['status' => 400]);
    }

    // Check if the new authors exist
    foreach ($new_author_ids as $new_author_id) {
      $new_author = get_user_by('id', $new_author_id);
      if (!$new_author) {
        return new WP_Error('invalid_new_author', 'One or more new authors do not exist', ['status' => 400]);
      }
    }

    // Get all posts of the author
    $posts = get_posts(['author' => $author_id, 'post_type' => 'post', 'numberposts' => -1]);

    $reassigned_posts = [];
    // Reassign the posts to the new authors
    foreach ($posts as $index => $post) {
      $post_id = $post->ID;
      $new_author_id = $new_author_ids[$index % count($new_author_ids)]; // Cycle through the new authors
      wp_update_post([
        'ID' => $post_id,
        'post_author' => $new_author_id,
      ]);

      // Fetch the updated post
      $updated_post = get_post($post_id);
      // Add the reassigned post to the array
      $reassigned_posts[] = [
      // 'new_author_ids' => $new_author_ids,
      'new_author_id' => $updated_post->post_author,
      'title' => $updated_post->post_title,
      ];
    }

    // Remove the author
    wp_delete_user($author_id);

    return rest_ensure_response([
      'message' => 'Author removed and posts reassigned successfully',
      'reassigned_posts' => $reassigned_posts,
    ]);
  }

  public function create_new_post(WP_REST_Request $data) {
    $author_id = $data['id'];
    $params = $data->get_json_params(); //Only return params from body
    $post_id = wp_insert_post([
      'post_title' => $params['title'],
      'post_content' => $params['content'],
      'post_status' => 'publish',
      'post_author' => $author_id,
    ]);
    // return $post_id;
    if (is_wp_error($post_id)) {
      return $post_id;
    }

    // Get created post
    $post = get_post($post_id);

    return rest_ensure_response([
      'message' => 'Post created successfully',
      'post_id' => $post_id,
      'title' => $post->post_title,
      'author' => $post->post_author,
      // 'author_name' => get_the_author_meta('display_name', $post->post_author),
    ]);
  }
}

new Rest_api();
