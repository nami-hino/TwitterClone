<?php
///////////////////////////////////////
// ツイートデータを処理
///////////////////////////////////////
 
/**
    * ツイート作成
    *
    * @param array $data
    * @return bool
    */
function createTweet(array $data)
{
    $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    // 接続チェック
    if ($mysqli->connect_errno) {
        echo 'MySQLの接続に失敗しました。：' . $mysqli->connect_error . "\n";
        exit;
    }
 
    // 新規登録のSQLを作成
    $query = 'INSERT INTO tweets (user_id, body, image_name) VALUES (?, ?, ?)';
    $statement = $mysqli->prepare($query);
 
    // 値をセット（i=int, s=string）
    $statement->bind_param('iss', $data['user_id'], $data['body'], $data['image_name']);
 
    // 処理を実行
    $response = $statement->execute();
    if ($response === false) {
        echo 'エラーメッセージ：' . $mysqli->error . "\n";
    }
 
    // 接続を閉じる
    $statement->close();
    $mysqli->close();
 
    return $response;
}
 
/**
    * ツイート一覧を取得
    *
    * @param array $user ログインしているユーザー情報
    * @return array|false
    */
function findTweets(array $user)
{
    $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    // 接続チェック
    if ($mysqli->connect_errno) {
        echo 'MySQLの接続に失敗しました。：' . $mysqli->connect_error . "\n";
        exit;
    }
 
    // ログインユーザーIDをエスケープ
    $login_user_id = $mysqli->real_escape_string($user['id']);
 
    // 検索のSQLを作成
    $query = <<<SQL
        SELECT
            T.id AS tweet_id,
            T.status AS tweet_status,
            T.body AS tweet_body,
            T.image_name AS tweet_image_name,
            T.created_at AS tweet_created_at,
            U.id AS user_id,
            U.name AS user_name,
            U.nickname AS user_nickname,
            U.image_name AS user_image_name,
            -- ログインユーザーがいいね！したか（している場合、値が入る）
            L.id AS like_id,
            -- いいね！数
            (SELECT COUNT(*) FROM likes WHERE status = 'active' AND tweet_id = T.id) AS like_count
        FROM
            tweets AS T
            -- ユーザーテーブルを紐付ける
            JOIN
            users AS U ON U.id = T.user_id AND U.status = 'active'
            -- いいね！テーブルを紐付ける
            LEFT JOIN
            likes AS L ON L.tweet_id = T.id AND L.status = 'active' AND L.user_id = '$login_user_id'
        WHERE
            T.status = 'active'
    SQL;
 
    // SQL実行
    if ($result = $mysqli->query($query)) {
        // データを配列で受け取る
        $response = $result->fetch_all(MYSQLI_ASSOC);
    } else {
        $response = false;
        echo 'エラーメッセージ：' . $mysqli->error . "\n";
    }
 
    $mysqli->close();
 
    return $response;
}