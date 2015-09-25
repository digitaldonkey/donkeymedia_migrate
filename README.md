This repository contains a custom module that migrated a WordPress blog (called My Mixing Bowl, hence the repository name) to the Drupal 8 site now found at tangledupinfood.com. It is posted as a reference / sample only.

I ended up feeling more comfortable using drush migrate-import to do each individual migration (files, posts, comments, ...) one at a time, rather than using drush migrate-manifest and a manifest file. This also allowed use of drush migrate-import --update to apply the results of changed process plugin code.

http://eworldproblems.mbaynton.com/2015/09/reset-a-crashed-migration-in-drupal-8/ may also prove useful if you do your own process plugins.
