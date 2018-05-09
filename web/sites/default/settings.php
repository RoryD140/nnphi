<?php

/**
 * Load services definition file.
 */
$settings['container_yamls'][] = __DIR__ . '/services.yml';

/**
 * Include the Pantheon-specific settings file.
 *
 * n.b. The settings.pantheon.php file makes some changes
 *      that affect all envrionments that this site
 *      exists in.  Always include this file, even in
 *      a local development environment, to ensure that
 *      the site settings remain consistent.
 */
include __DIR__ . "/settings.pantheon.php";

/**
 * Place the config directory outside of the Drupal root.
 */
$config_directories = array(
  CONFIG_SYNC_DIRECTORY => dirname(DRUPAL_ROOT) . '/config/default',
);

/**
 * Always install the 'standard' profile to stop the installer from
 * modifying settings.php.
 *
 * See: tests/installer-features/installer.feature
 */
$settings['install_profile'] = 'standard';

/**
 * Load Algolia credentials and settings.
 */
$index = 'dev_TRAINING';

// Set GA account ID empty by default so that only prod is tracked
$config['google_analytics.settings']['account'] = '';

if (isset($_ENV['PANTHEON_ENVIRONMENT'])) {
  $json_text = file_get_contents($site_path . '/files/private/secrets.json');
  $secrets = json_decode($json_text, TRUE);
  if (isset($secrets['algolia_key'])) {
    $config['search_api.server.algolia']['backend_config']['api_key'] = $secrets['algolia_key'];
  }
  // Switch to the correct Algolia index.
  $index = 'dev_TRAINING';
  $indexes = [
    'live' => 'prod_TRAINING',
    'test' => 'test_TRAINING',
    'dev' => 'dev_TRAINING',
  ];
  if (isset($indexes[$_ENV['PANTHEON_ENVIRONMENT']])) {
    // Indexing is disabled by default, enable on servers.
    $config['search_api.index.training']['read_only'] = FALSE;
    $index = $indexes[$_ENV['PANTHEON_ENVIRONMENT']];
  }

  $senders = [
    'live' => 'noreply@phlearningnavigator.org',
    'test' => 'test-noreply@phlearningnavigator.org',
    'dev' => 'dev-noreply@phlearningnavigator.org'
  ];
  if (isset($senders[$_ENV['PANTHEON_ENVIRONMENT']])) {
    $config['system.site']['mail'] = $senders[$_ENV['PANTHEON_ENVIRONMENT']];
    if (isset($secrets['mailgun_key'])) {
      $config['mailgun.settings']['api_key'] = $secrets['mailgun_key'];
    }
  }
  else {
    $config['system.site']['mail'] = 'dev-noreply@phlearningnavigator.org';
    $config['mailsystem.settings']['defaults']['sender'] = 'php_mail';
    $config['mailsystem.settings']['modules']['webform']['none']['sender'] = 'php_mail';
  }


  // Set Google Analytics ID on Prod
  if ($_ENV['PANTHEON_ENVIRONMENT'] === 'live') {
    $config['google_analytics.settings']['account'] = 'UA-109080080-1';
  }

  // Redis settings. @see https://pantheon.io/docs/drupal-redis/
  // Include the Redis services.yml file. Adjust the path if you installed to a contrib or other subdirectory.
  $settings['container_yamls'][] = '../../modules/contrib/redis/example.services.yml';

  //phpredis is built into the Pantheon application container.
  $settings['redis.connection']['interface'] = 'PhpRedis';
  // These are dynamic variables handled by Pantheon.
  $settings['redis.connection']['host']      = $_ENV['CACHE_HOST'];
  $settings['redis.connection']['port']      = $_ENV['CACHE_PORT'];
  $settings['redis.connection']['password']  = $_ENV['CACHE_PASSWORD'];

  // Set up social login secrets.
  if (isset($secrets['facebook_secret'])) {
    $config['social_auth_facebook.settings']['app_secret'] = $secrets['facebook_secret'];
  }
  if (isset($secrets['linkedin_secret'])) {
    $config['social_auth_linkedin.settings']['client_secret'] = $secrets['linkedin_secret'];
  }
  if (isset($secrets['google_secret'])) {
    $config['social_auth_google.settings']['client_secret'] = $secrets['linkedin_secret'];
  }
}
else {
  // Enable the development config_split
  $config['config_split.config_split.development']['status'] = TRUE;
}
// Set the search_api and training search page index.
$config['search_api.index.training']['options']['algolia_index_name'] = $index;
$config['nnphi_training.search.config']['index'] = $index;

// Normalizing & enforcing https for live site. Todo: once pre-prods have domains and certs, add logic to cover them. 
if (isset($_ENV['PANTHEON_ENVIRONMENT']) && php_sapi_name() != 'cli') {
  // Redirect to https://$primary_domain in the Live environment
  if ($_ENV['PANTHEON_ENVIRONMENT'] === 'live') {
    /** Replace www.example.com with your registered domain name */
    $primary_domain = 'www.phlearningnavigator.org';
  }
}


/**
 * If there is a local settings file, then include it
 */
$local_settings = __DIR__ . "/settings.local.php";
if (file_exists($local_settings)) {
  include $local_settings;
}