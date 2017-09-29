<?php
//Clear all cache
echo "Rebuilding cache.\n";
passthru('drush cr');

// Run database updates.
echo "Running database updates...\n";
passthru('drush updb -y');

// Import all config changes.
echo "Importing configuration from yml files...\n";
passthru('drush config-import -y');
echo "Import of configuration complete.\n";

//  Run entity updates
echo "Running entity updates...\n";
passthru('drush entup -y');

// Clear varnish.
echo "Clearning edge cache...\n";
passthru('drush -- cc all');

echo "Deployment updates complete.\n";
