<?php
declare(strict_types=1);

/**
 * SQLiteデータベースへ接続する関数
 */
function getDb(): PDO
{
    static $db = null;

    // 同じ処理中ですでに接続していたら再利用する
    if ($db instanceof PDO) {
        return $db;
    }

    $databasePath = __DIR__ . '/../data/style_share.db';
    $dataDirectory = dirname($databasePath);

    // dataフォルダが存在しない場合は作成する
    if (!is_dir($dataDirectory)) {
        mkdir($dataDirectory, 0777, true);
    }

    $db = new PDO('sqlite:' . $databasePath);

    // SQLで問題が起きたときに例外を発生させる
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 取得結果を連想配列にする
    $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    // SQLiteの外部キー制約を有効にする
    $db->exec('PRAGMA foreign_keys = ON');

    return $db;
}
