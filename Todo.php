<?php

date_default_timezone_set('Asia/Tokyo');

require 'vendor/autoload.php';

use Cassandra\Date;
use Dotenv\Dotenv;

class Todo
{
    private $dotenv;
    private $dbh;

    // コンストラクタ
    public function __construct()
    {
        $this->dotenv = Dotenv::createImmutable(__DIR__);
        $this->dotenv->load();

        $this->dbh = new PDO('mysql:dbname='.$_ENV['DB_NAME'].';host=127.0.0.1', $_ENV['DB_USER'], $_ENV['DB_PASSWORD']);
    }

    public function getList()
    {
        $stmt = $this->dbh->query("SELECT * FROM `todo` WHERE `deleted_at` IS NULL ORDER BY `created_at` ASC");
        return $stmt->fetchAll();
    }

    /**
     * @param string $title
     * @param Date $due_date
     */
    public function post(string $title, Date $due_date)
    {
        $stmt = $this->dbh->prepare("INSERT INTO `todo` (title, due_date) VALUES (:title, :due_date)");
        $stmt->bindParam(':title', $title, PDO::PARAM_STR);
        $stmt->bindParam(':due_date', $due_date, PDO::PARAM_DATE);
        $stmt->execute();
    }

    /**
     * @param int $id
     * @param int $status
     */
    public function update(int $id, int $status) {
        $sql = "UPDATE `todo` SET status = :status WHERE id = :id";
        $stmt = $this->dbh->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':status', $status, PDO::PARAM_INT);
        $stmt->execute();
    }

    public function delete()
    {
        $sql = "UPDATE `todo` SET `deleted_at` = NOW()";
        $stmt = $this->dbh->prepare($sql);
        $stmt->execute();
    }
}