<?php
/*
Plugin Name: RSS Feed Ticker
Description: Display an RSS feed, custom static content, or  posts with drag and drop selection as a styled scrolling ticker with a custom label, effect, and navigation.
Version: 1.5.1
Author: Muhammad Mudassir by Label Agency
*/

// Enqueue Scripts and Styles
function rssfeedticker_enqueue_scripts() {
  wp_enqueue_style('rssfeedticker-style', plugin_dir_url(__FILE__) . 'css/style.css');
  wp_enqueue_script('rssfeedticker-script', plugin_dir_url(__FILE__) . 'js/ticker.js', array('jquery'), null, true);
}
add_action('wp_enqueue_scripts', 'rssfeedticker_enqueue_scripts', 90);
function rssfeedticker_enqueue_admin_scripts($hook) {
    // Only load on the specific plugin settings page
    if ($hook !== 'settings_page_rss-feed-ticker') {
        return;
    }
    
    wp_enqueue_script('jquery-ui-sortable');
    wp_enqueue_script('rssfeedticker-admin-script', plugin_dir_url(__FILE__) . 'js/admin.js', array('jquery', 'jquery-ui-sortable'), null, true);
    wp_enqueue_style('rssfeedticker-admin-style', plugin_dir_url(__FILE__) . 'css/admin.css');
}
add_action('admin_enqueue_scripts', 'rssfeedticker_enqueue_admin_scripts');

// Register the settings page
function rssfeedticker_register_settings() {
  add_options_page(
    'RSS Feed Ticker Settings',
    'RSS Feed Ticker',
    'manage_options',
    'rss-feed-ticker',
    'rssfeedticker_settings_page'
  );
}
add_action('admin_menu', 'rssfeedticker_register_settings');

// Register settings
function rssfeedticker_register_options() {
  register_setting('rssfeedticker_options', 'rssfeedticker_content_type'); // New content type setting
  register_setting('rssfeedticker_options', 'rssfeedticker_feed_url');
  register_setting('rssfeedticker_options', 'rssfeedticker_static_content'); // New static content field
  register_setting('rssfeedticker_options', 'rssfeedticker_static_link'); // Link for static content
  register_setting('rssfeedticker_options', 'rssfeedticker_speed');
  register_setting('rssfeedticker_options', 'rssfeedticker_label_text');
  register_setting('rssfeedticker_options', 'rssfeedticker_posts_number'); // Number of posts
  register_setting('rssfeedticker_options', 'rssfeedticker_posts_order'); // Order of posts
  register_setting('rssfeedticker_options', 'rssfeedticker_selected_posts'); // New setting for selected posts (comma-separated IDs)
}
add_action('admin_init', 'rssfeedticker_register_options');

// Settings page
function rssfeedticker_settings_page() {
  ?>
  <div class="wrap">
    <h1>RSS Feed Ticker Settings</h1>
    <p>Use the shortcode [rss_feed_ticker] to display the ticker anywhere on your site.</p>
    <form method="post" action="options.php">
      <?php settings_fields('rssfeedticker_options'); ?>
      <?php do_settings_sections('rssfeedticker_options'); ?>
      <table class="form-table">
        <tr valign="top">
          <th scope="row">Content Type</th>
          <td>
  <label>
    <input type="radio" name="rssfeedticker_content_type" value="rss" <?php checked(get_option('rssfeedticker_content_type'), 'rss'); ?> />
    RSS Feed
  </label>
  <br>
  <label>
    <input type="radio" name="rssfeedticker_content_type" value="static" <?php checked(get_option('rssfeedticker_content_type'), 'static'); ?> />
    Static Content
  </label>
  <br>
  <label>
    <input type="radio" name="rssfeedticker_content_type" value="posts" <?php checked(get_option('rssfeedticker_content_type'), 'posts'); ?> />
    WordPress Posts
  </label>
</td>
        </tr>
        <tr valign="top" id="rssfeedticker_feed_url_row">
          <th scope="row">RSS Feed URL</th>
          <td><input style="width: 100%;" type="text" name="rssfeedticker_feed_url" value="<?php echo esc_attr(get_option('rssfeedticker_feed_url')); ?>" /></td>
        </tr>
        <tr valign="top" id="rssfeedticker_static_content_row">
          <th scope="row">Static Content</th>
          <td><textarea style="width: 100%; height: 100px;" name="rssfeedticker_static_content"><?php echo esc_textarea(get_option('rssfeedticker_static_content')); ?></textarea></td>
        </tr>
        <tr valign="top" id="rssfeedticker_posts_number_row" style="display:none;">
          <th scope="row">Number of Posts</th>
          <td>
            <input style="width: 100%;" type="number" name="rssfeedticker_posts_number" value="<?php echo esc_attr(get_option('rssfeedticker_posts_number', 5)); ?>" min="1" />
          </td>
        </tr>
        <tr valign="top" id="rssfeedticker_posts_order_row" style="display:none;">
          <th scope="row">Order</th>
          <td>
            <select name="rssfeedticker_posts_order" id="rssfeedticker_posts_order">
              <option value="asc" <?php selected(get_option('rssfeedticker_posts_order', 'asc'), 'asc'); ?>>Ascending</option>
              <option value="desc" <?php selected(get_option('rssfeedticker_posts_order', 'desc'), 'desc'); ?>>Descending</option>
            </select>
          </td>
        </tr>
        <tr valign="top" id="rssfeedticker_selected_posts_row" style="">
    <th scope="row">Select Posts</th>
    <td>
        <div class="rssfeedticker-posts-selector">
            <div class="rssfeedticker-all-posts">
                <h4>Available Posts</h4>
                <ul id="all-posts-list" class="rssfeedticker-sortable">
                    <?php
                    $all_posts = get_posts(array('post_type' => 'post', 'numberposts' => -1));
                    $selected_posts = explode(',', get_option('rssfeedticker_selected_posts', ''));
                    $selected_post_ids = array_map('intval', $selected_posts);

                    foreach ($all_posts as $post) {
                        if (!in_array($post->ID, $selected_post_ids)) {
                            echo '<li data-id="' . esc_attr($post->ID) . '">' . esc_html($post->post_title) . '</li>';
                        }
                    }
                    ?>
                </ul>
            </div>
            <div class="rssfeedticker-selected-posts">
                <h4>Selected Posts</h4>
                <ul id="selected-posts-list" class="rssfeedticker-sortable">
                    <?php
                    foreach ($selected_post_ids as $post_id) {
                        $post = get_post($post_id);
                        if ($post) {
                            echo '<li data-id="' . esc_attr($post->ID) . '">' . esc_html($post->post_title) . '</li>';
                        }
                    }
                    ?>
                </ul>
                <input type="hidden" name="rssfeedticker_selected_posts" id="rssfeedticker_selected_posts" value="<?php echo esc_attr(implode(',', $selected_post_ids)); ?>" />
            </div>
        </div>
    </td>
</tr>
        <tr valign="top" id="rssfeedticker_static_link_row">
          <th scope="row">Static Content Link</th>
          <td><input style="width: 100%;" type="text" name="rssfeedticker_static_link" value="<?php echo esc_attr(get_option('rssfeedticker_static_link')); ?>" /></td>
        </tr>
        <tr valign="top" id="rssfeedticker_speed_row">
          <th scope="row">Ticker Speed (ms)</th>
          <td><input style="width: 100%;" type="number" name="rssfeedticker_speed" value="<?php echo esc_attr(get_option('rssfeedticker_speed', 5000)); ?>" /></td>
        </tr>
        <tr valign="top">
          <th scope="row">Label Text</th>
          <td><input style="width: 100%;" type="text" name="rssfeedticker_label_text" value="<?php echo esc_attr(get_option('rssfeedticker_label_text', 'NEWS')); ?>" /></td>
        </tr>
      </table>
      <?php submit_button(); ?>
    </form>
  </div>

  <script>
  // JavaScript to toggle fields based on content type selection
  document.addEventListener('DOMContentLoaded', function () {
    const contentType = document.getElementById('rssfeedticker_content_type');
    const rssFeedUrlRow = document.getElementById('rssfeedticker_feed_url_row');
    const staticContentRow = document.getElementById('rssfeedticker_static_content_row');
    const postsNumberRow = document.getElementById('rssfeedticker_posts_number_row');
    const postsOrderRow = document.getElementById('rssfeedticker_posts_order_row');
    const selectedPostsRow = document.getElementById('rssfeedticker_selected_posts_row');
    const staticLinkRow = document.getElementById('rssfeedticker_static_link_row');
    const tickerSpeedRow = document.getElementById('rssfeedticker_speed_row'); // New row for ticker speed

    function toggleFields() {
      if (contentType.value === 'rss') {
        rssFeedUrlRow.style.display = 'table-row';
        staticContentRow.style.display = 'none';
        postsNumberRow.style.display = 'none';
        postsOrderRow.style.display = 'none';
        selectedPostsRow.style.display = 'none';
        staticLinkRow.style.display = 'none'; // Hide static link row
        tickerSpeedRow.style.display = 'table-row'; // Show ticker speed row
      } else if (contentType.value === 'static') {
        rssFeedUrlRow.style.display = 'none';
        staticContentRow.style.display = 'table-row';
        postsNumberRow.style.display = 'none';
    selectedPostsRow.style.display = 'none';
        staticLinkRow.style.display = 'table-row'; // Show static link row
        tickerSpeedRow.style.display = 'none'; // Hide ticker speed row
      } else if (contentType.value === 'posts') {
        rssFeedUrlRow.style.display = 'none';
        staticContentRow.style.display = 'none';
        postsNumberRow.style.display = 'table-row';
        postsOrderRow.style.display = 'table-row';
        selectedPostsRow.style.display = 'table-row'; // Show selected posts row
        staticLinkRow.style.display = 'none'; // Hide static link row
        tickerSpeedRow.style.display = 'table-row'; // Show ticker speed row
      }
    }

    contentType.addEventListener('change', toggleFields);
    toggleFields(); // Initial call to set up correct field display
  });
</script>

<?php
}

// Display the ticker based on selected content type
function rssfeedticker_display_ticker() {
  $content_type = get_option('rssfeedticker_content_type', 'rss');
  $speed = get_option('rssfeedticker_speed', 3000);
  $label_text = get_option('rssfeedticker_label_text', 'NEWS');

  ob_start(); ?>
  <div class="rss-feed-ticker-wrapper <?php echo ($content_type === 'static') ? 'static-content-mode' : ''; ?>">
    <div class="rss-feed-ticker-header">
      <span class="news-label"><?php echo esc_html($label_text); ?></span>
    </div>
    <div id="rss-feed-ticker" data-speed="<?php echo esc_attr($speed); ?>">
      <?php if ($content_type === 'rss') : ?>
        <?php
        $feed_url = get_option('rssfeedticker_feed_url');
        if (!$feed_url) {
          return '<p>Please configure the RSS feed URL in the settings.</p>';
        }
        $rss = fetch_feed($feed_url);
        if (is_wp_error($rss)) {
          return '<p>Unable to fetch feed.</p>';
        }
        $rss_items = $rss->get_items(0, 10);
        if (!$rss_items) {
          return '<p>No items found in the feed.</p>';
        }
        foreach ($rss_items as $item) : ?>
          <div class="ticker-item">
            <span class="item-date"><?php echo esc_html($item->get_date('F j, Y')); ?></span>
            <span class="item-title">
              <a style="text-decoration: none;" href="<?php echo esc_url($item->get_permalink()); ?>" target="_blank"><?php echo esc_html($item->get_title()); ?></a>
            </span>
          </div>
        <?php endforeach; ?>
      <?php elseif ($content_type === 'static') : ?>
        <div class="marquee">
          <a style="text-decoration: none;" href="<?php echo esc_url(get_option('rssfeedticker_static_link', '#')); ?>">
            <span class="marquee-content"><?php echo esc_html(get_option('rssfeedticker_static_content')); ?></span>
          </a>
        </div>
      <?php elseif ($content_type === 'posts') : ?>
        <?php
        $selected_posts = explode(',', get_option('rssfeedticker_selected_posts'));
        $posts = array_map('intval', $selected_posts); // Convert to integer IDs

        if (!empty($posts)) {
          $args = array(
            'post__in' => $posts,
            'post_type' => 'post',
            'post_status' => 'publish',
            'orderby' => get_option('rssfeedticker_posts_order', 'asc'),
            'order' => get_option('rssfeedticker_posts_order', 'asc')
          );
          $recent_posts = get_posts($args);

          if ($recent_posts) {
            foreach ($recent_posts as $post) : ?>
              <div class="ticker-item">
                <span class="item-date"><?php echo esc_html(get_the_date('F j, Y', $post->ID)); ?></span>
                <span class="item-title">
                  <a style="text-decoration: none;" href="<?php echo esc_url(get_permalink($post->ID)); ?>" target="_blank"><?php echo esc_html($post->post_title); ?></a>
                </span>
              </div>
            <?php endforeach;
          } else {
            echo '<p>No posts found for the selected IDs.</p>';
          }
        } else {
          echo '<p>Please enter valid post IDs in the settings.</p>';
        }
        ?>
      <?php endif; ?>
    </div>
    <?php if ($content_type === 'rss' || $content_type === 'posts') : ?>
      <button class="ticker-nav prev">&lt;</button>
      <button class="ticker-nav next">&gt;</button>
    <?php endif; ?>
  </div>

  <style>
    /* Marquee-style scroll for static content */
    .static-content-mode .marquee {
      overflow: hidden;
      white-space: nowrap;
      width: 100%;
    }

    .static-content-mode .marquee-content {
      display: inline-block;
      padding-left: 100%;
      animation: marquee linear infinite;
    }

    @keyframes marquee {
      0%  { transform: translateX(0); }
      100% { transform: translateX(-100%); }
    }

    /* Hide navigation buttons in static content mode */
    .static-content-mode .ticker-nav {
      display: none;
    }
  </style>

  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const marqueeContent = document.querySelector('.marquee-content');
      
      if (marqueeContent) {
        const contentWidth = marqueeContent.offsetWidth;
        const containerWidth = marqueeContent.parentElement.offsetWidth;
        const scrollSpeed = 100; // pixels per second, adjust for readability

        // Calculate duration based on width and desired scroll speed
        const animationDuration = (contentWidth + containerWidth) / scrollSpeed;

        // Set the calculated duration on the animation
        marqueeContent.style.animationDuration = `${animationDuration}s`;
      }
    });
  </script>
  <?php
  return ob_get_clean();
}
add_shortcode('rss_feed_ticker', 'rssfeedticker_display_ticker');

// post id 