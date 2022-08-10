<?php
///////////////////////
/// フォローコントローラー
//////////////////////

// 設定を読み込み
include_once '../config.php';
// 便利な関数を読み込み
include_once '../util.php';
//フォロー操作モデルを読み込み
include_once '../Models/follows.php';
// 通知データ操作モデルを読み込む
include_once '../Models/notifications.php';

// ログインチェック
$user = getUserSession();
if (!$user) {
    // 404エラー
header('HTTP/1.0 404 Not Found');
    exit;
}

// ------
// フォローする
// --------
$follow_id = null;
// followed_user_id がポストされた場合
if (isset($_POST['followed_user_id'])) {
    $data = [
        'followed_user_id' => $_POST['followed_user_id'],
        'follow_user_id' => $user['id'],
    ];
    // フォロー登録
    $follow_id = createFollow($data);

    // 通知を登録
    $data_notification = [
        'received_user_id' => $_POST['followed_user_id'],
        'sent_user_id' => $user['id'],
        'message' => 'フォローされました。',
    ];
    createNotification($data_notification);
}

// ------
// フォロー解除
// -----

// follow_idがPOSTされた場合
if (isset($_POST['follow_id'])) {
    $data = [
    'follow_id' => $_POST['follow_id'],
    'follow_user_id' => $user['id'],
];
// フォロー解除
    deleteFollow($data);

}

// -----
// JSON形式で結果を返却
// ------
$response = [
    'message' => 'successful',
    // フォローした時に値が入る
    'follow_id' => $follow_id,
];
header('Content-Type: application/json; charset=uft-8');
echo json_encode($response);