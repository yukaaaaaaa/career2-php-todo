<?php

date_default_timezone_set('Asia/Tokyo');

require 'vendor/autoload.php';

use Dotenv\Dotenv;

class Todo
{
    private $dotenv;
    private $dbh;

    const STATUS = [
        '未着手',
        '作業中',
        '完了',
    ];

    // コンストラクタ
    public function __construct()
    {
        $this->dotenv = Dotenv::createImmutable(__DIR__);
        $this->dotenv->load();

        $this->dbh = new PDO('mysql:dbname='.$_ENV['DB_NAME'].';host=127.0.0.1', $_ENV['DB_USER'], $_ENV['DB_PASSWORD']);
    }

    /**
     * タスクを昇順に取得する
     * @return array
     */
    public function getList()
    {
        $stmt = $this->dbh->query("SELECT * FROM `todo` WHERE `deleted_at` IS NULL ORDER BY `due_date` ASC");
        return array_map(function ($todo) {
            $todo["status"] = intval($todo["status"]);
            $todo["status_for_display"] = self::STATUS[$todo["status"]];
            return $todo;
        }, $stmt->fetchAll());
    }

    /**
     * タスクを保存する
     * @param string $title
     * @param string $due_date
     * @param array|null $image_file
     */
    public function post(string $title, string $due_date, array $image_file = null)
    {
        $image = null;
        if (!empty($image_file) && !empty($image_file['name'])) {
            // ファイル名をユニーク化
            $image = uniqid(mt_rand(), true);
            // アップロードされたファイルの拡張子を取得
            $image .= '.' . substr(strrchr($image_file['name'], '.'), 1);
            // uploadディレクトリにファイル保存
            move_uploaded_file($image_file['tmp_name'], './upload/' . $image);
        }
        $stmt = $this->dbh->prepare("INSERT INTO `todo` (title, due_date, image) VALUES (:title, :due_date, :image)");
        $stmt->bindParam(':title', $title, PDO::PARAM_STR);
        $stmt->bindParam(':due_date', $due_date, PDO::PARAM_STR);
        $stmt->bindParam(':image', $image, PDO::PARAM_STR);
        $stmt->execute();
    }

    /**
     * タスクを更新する
     * @param int $id
     * @param int $status
     */
    public function update(int $id, int $status)
    {
        $sql = "UPDATE `todo` SET status = :status WHERE id = :id";
        $stmt = $this->dbh->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':status', $status, PDO::PARAM_INT);
        $stmt->execute();
    }

    /**
     * タスクを全削除する
     */
    public function deleteAll()
    {
        $sql = "UPDATE `todo` SET `deleted_at` = NOW()";
        $stmt = $this->dbh->prepare($sql);
        $stmt->execute();
    }

    /**
     * タスクを削除する
     * @param int $id
     */
    public function delete(int $id)
    {
        $sql = "UPDATE `todo` SET `deleted_at` = NOW() WHERE id = :id";
        $stmt = $this->dbh->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
    }
}