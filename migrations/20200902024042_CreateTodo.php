<?php

use Phpmig\Migration\Migration;

class CreateTodo extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $sql = <<<EOT
CREATE TABLE IF NOT EXISTS `todo` (
`id` int(11) AUTO_INCREMENT,
`title` varchar(255) NOT NULL,
`due_date` DATE NOT NULL,
`status` tinyint(1) NOT NULL DEFAULT 0,
`created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
`deleted_at` DATETIME DEFAULT NULL,
PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8mb4;
EOT;
        $container = $this->getContainer();
        $container['db']->query($sql);
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $sql = "DROP TABLE `todo`;";
        $container = $this->getContainer();
        $container['db']->query($sql);
    }
}
