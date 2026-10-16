<?php

namespace Fire\Robo\Plugin\Commands;

use Robo\Robo;
use Robo\Symfony\ConsoleIO;

/**
 * Provides a database sanitization command.
 */
class SanitizeDBCommand extends FireCommandBase {

  /**
   * Sanitizes user data in the local database.
   *
   * Usage Example: fire db:san
   *
   * @command db:san
   * @aliases db-san
   */
  public function sanitizeDB(ConsoleIO $io) {
    $env = Robo::config()->get('local_environment');
    $ignoredRoles = "'admin', 'administrator', 'dev', 'developer'";
    $ignoredUsers = "NOT EXISTS (SELECT 1 FROM user__roles ur WHERE ur.entity_id = u.uid AND ur.roles_target_id IN ($ignoredRoles))";
    $sanitizedSuffix = "CONCAT('_sanitized_', u.uid)";
    $baseName = "CASE WHEN RIGHT(u.name, CHAR_LENGTH($sanitizedSuffix)) = $sanitizedSuffix THEN LEFT(u.name, CHAR_LENGTH(u.name) - CHAR_LENGTH($sanitizedSuffix)) ELSE SUBSTRING_INDEX(u.name, '@', 1) END";
    $normalizedName = "REGEXP_REPLACE(REGEXP_REPLACE(TRIM($baseName), '[^A-Za-z0-9._-]+', '_'), '^[._-]+|[._-]+$', '')";
    $normalizedName = "COALESCE(NULLIF($normalizedName, ''), CONCAT('user_', u.uid))";
    $sanitizedEmail = "CONCAT($normalizedName, '@sanitized.localdomain')";

    $tasks = $this->collectionBuilder($io);
    $tasks->addTask($this->taskExec($env . " drush sql:sanitize -y --sanitize-email='%name@sanitized.localdomain' --ignored-roles='admin,administrator,dev,developer'"));
    $tasks->addTask($this->taskExec($env . " drush sql:query \"UPDATE users_field_data u SET u.mail = $sanitizedEmail, u.init = $sanitizedEmail, u.name = CONCAT(LEFT($normalizedName, 60 - CHAR_LENGTH($sanitizedSuffix)), $sanitizedSuffix) WHERE u.uid > 1 AND $ignoredUsers;\""));
    $tasks->addTask($this->taskExec($env . ' drush cr'));
    return $tasks;
  }

}
