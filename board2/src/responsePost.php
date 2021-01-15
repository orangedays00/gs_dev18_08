<?php
$webRoot = $_SERVER['DOCUMENT_ROOT'];

/* --------------------- DBとの接続オブジェクトを取得 --------------------- */
include_once($webRoot."/board2/src/function.php");
$dbh = getDbh();

/* 文字化け防止 */
header("Content-type: text/plain; charset=UTF-8");
date_default_timezone_set('Asia/Tokyo');

/* ------------------------------ 変数に格納 ------------------------------ */
/* スレ主フラグ */
$isMain = $_POST['isMain'];$pass="";$name="";

/* スレッド主の場合 */
if($isMain == 1){
  /* クローズフラグ */
  $closeFlg = $_POST['close'];

  /* 編集・削除用パス */
  $respass = $_POST['respass'];
  $respass = htmlspecialchars($respass, ENT_QUOTES, 'UTF-8');
  $respass = hash("sha256",$respass);

  /* パス */
  $pass = $_POST['pass'];
  $pass = htmlspecialchars($pass, ENT_QUOTES, 'UTF-8');
  $pass = hash("sha256",$pass);

/* スレッド以外主の場合 */
}else{
  /* 名前 */
  $name = $_POST['name'];
  $name = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
  /* 閉じるフラグ。回答者に決定権がないので2で固定。 */
  $closeFlg = "0";

  /* 編集・削除用パス */
  $respass = $_POST['respass'];
  $respass = htmlspecialchars($respass, ENT_QUOTES, 'UTF-8');
  $respass = hash("sha256",$respass);
}

/* スレッドID */
$thread_id = $_POST['thread_id'];
/* メッセージ */
$message = $_POST['message'];
$message = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');


/* ------------------------------ チェック処理 ------------------------------ */

/* スペースだけの投稿防止 */
$blank = array('　',' ');
$checkName = str_replace($blank, "", $name);
$checkMessage = str_replace($blank, "", $message);
$checkPass = str_replace($blank, "", $pass);

if($isMain != 1 && $checkName == ""){
  p('noName');
  return ;
}
if($checkMessage==""){
  p('noMsg');
  return ;
}
if($isMain == 1 && $checkPass == ""){
  p('noPass');
  return ;
}
/* ------------------------------ 登録準備 ------------------------------ */
if($isMain == 1){
  /* スレ主の場合、パスワードをチェックする */
  $sql="SELECT RD.name,TD.pass
      FROM gs_thread_data TD,gs_res_data RD
        WHERE TD.thread_id = RD.thread_id
          AND TD.thread_id = :thread_id
          AND RD.userType = 1";
  $stmt = $dbh->prepare($sql);
  $stmt->bindParam(':thread_id', $thread_id, PDO::PARAM_STR);
  $stmt->execute();
  $result = $stmt->fetch(PDO::FETCH_ASSOC);

  /* パスワードが最初の投稿と一致しているかチェック */
  if($pass != $result['pass']){
      p('passDif');
      return;
  }

  /* 入力させない代わりにここで取得 */
  $name = $result['name'];
}

/* スレッド内の最新の番号を取得する。 */
// $countSql = "SELECT MAX(message_id)
//         FROM gs_res_data
//         WHERE thread_id = :thread_id";
// $stmt = $dbh->prepare($countSql);
// $stmt->bindParam(':thread_id', $thread_id, PDO::PARAM_STR);
// $stmt->execute();
// $maxValue = $stmt->fetchColumn();
// $maxValue = $maxValue + 1;
// メッセージID
$message_id = date(YmdHis);

try{
  /* トランザクション制御 */
  $dbh->beginTransaction();

  /* メッセージテーブル(board2_message)への登録 */
  $sql="INSERT INTO gs_res_data(thread_id,message_id,messageType,userType,name,message,pass,deleteFlg,disabledFlg,createTime,updateTime)VALUES(:thread_id,:message_id,'1',:userType,:name,:message,:pass,'0','0',sysdate(), sysdate())";

  $stmt = $dbh->prepare($sql);
  $stmt->bindParam(':thread_id', $thread_id, PDO::PARAM_STR);
  $stmt->bindParam(':message_id', $message_id, PDO::PARAM_STR);
  $stmt->bindParam(':userType', $isMain, PDO::PARAM_STR);
  $stmt->bindParam(':name', $name, PDO::PARAM_STR);
  $stmt->bindParam(':message', $message, PDO::PARAM_STR);
  $stmt->bindParam(':pass', $respass, PDO::PARAM_STR);

  $stmt->execute();

  /* スレッドテーブル（gs_thread_data）の更新（表示順の為） */
  $sql="UPDATE gs_thread_data
        SET updateTime = :updateTime,
          closeFlg = :closeFlg
        WHERE thread_id = :thread_id";
  $stmt = $dbh->prepare($sql);
  $nowDate = date("Y-m-d H:i:s");
  $stmt->bindParam(':updateTime', $nowDate,  PDO::PARAM_STR);
  $stmt->bindParam(':closeFlg', $closeFlg, PDO::PARAM_STR);
  $stmt->bindParam(':thread_id', $thread_id, PDO::PARAM_STR);
  $stmt->execute();

  /* コミット（処理を確定） */
  $dbh->commit();
} catch (Exception $e) {
  $dbh->rollBack();
  echo "失敗しました。" . $e->getMessage();
}

?>