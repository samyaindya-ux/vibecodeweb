<?php
/**
 * Copy to deploy_secret.php and set a real secret. NEVER commit deploy_secret.php.
 *
 * Generate one with:
 *   php -r "echo bin2hex(random_bytes(24)), PHP_EOL;"
 *
 * The same value goes into the repo's GitHub Secrets as DEPLOY_SECRET.
 */
return 'CHANGE_ME';
