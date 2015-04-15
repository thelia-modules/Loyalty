
# This is a fix for InnoDB in MySQL >= 4.1.x
# It "suspends judgement" for fkey relationships until are tables are set.
SET FOREIGN_KEY_CHECKS = 0;

-- ---------------------------------------------------------------------
-- loyalty
-- ---------------------------------------------------------------------

DROP TABLE IF EXISTS `loyalty`;

CREATE TABLE `loyalty`
(
    `id` INTEGER NOT NULL AUTO_INCREMENT,
    `min` FLOAT,
    `max` FLOAT,
    `amount` FLOAT,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB;

# This restores the fkey checks, after having unset them earlier
SET FOREIGN_KEY_CHECKS = 1;
