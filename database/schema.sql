CREATE TABLE `todo_api`.`tasks` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(255) NOT NULL,
  `description` TEXT NULL,
  `priority` ENUM('low','medium','high') NOT NULL DEFAULT 'medium',
  `due_date` DATETIME NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `status` ENUM('pending','in_progress','done') NOT NULL DEFAULT 'pending',
  PRIMARY KEY (`id`)
) ENGINE = InnoDB;