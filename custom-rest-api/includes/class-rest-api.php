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
}

new Rest_api();
