<?php

$webRoot = $_SERVER['DOCUMENT_ROOT'];
require_once($webRoot."/board2/src/function.php");

$dbh = getDbh();

date_default_timezone_set('Asia/Tokyo');

$thread_id = $_POST['thread_id'];
// var_dump($thread_id);

$password = $_POST['post_password'];
$pass = h($password);
$pass = hash("sha256",$pass);

// var_dump($pass);

$id = $_GET['id'];

if(empty($password)) {
  $webRoot = (empty($_SERVER['HTTPS']) ? 'http://' : 'https://') . $_SERVER['HTTP_HOST'];
  $url = $webRoot . "/board2/thread/" . $thread_id . "/";
  
    p('<div>パスワードを入力してください</div>
    <a href="' . $url . '">戻る</a>');
    return;
} else {
  if(!empty($pass)) {
    $sql="SELECT RD.pass
          FROM gs_res_data RD
            WHERE RD.message_id = :res_id";
      $stmt = $dbh->prepare($sql);
      $stmt->bindParam(':res_id', $id, PDO::PARAM_STR);
      $stmt->execute();
      $result = $stmt->fetch(PDO::FETCH_ASSOC);
      var_dump(($result['pass']));

      /* パスワードが最初の投稿と一致しているかチェック */
      if($pass != $result['pass']){
        $webRoot = (empty($_SERVER['HTTPS']) ? 'http://' : 'https://') . $_SERVER['HTTP_HOST'];
        $url = $webRoot . "/board2/thread/" . $thread_id . "/";

          p('<div>パスワードが間違っています</div>
          <a href="' . $url . '">戻る</a>');
          return;
      } else {
        $sql = 'DELETE FROM gs_res_data WHERE message_id = :id';

        $stmt = $dbh->prepare($sql);
        $stmt->bindValue(':id', h($id), PDO::PARAM_INT);
        $status = $stmt->execute();

      // var_dump($status);

      if($status == false) {
        sql_error($status);
      } else {
        $webRoot = (empty($_SERVER['HTTPS']) ? 'http://' : 'https://') . $_SERVER['HTTP_HOST'];
        $url = $webRoot . "/board2/thread/" . $thread_id . "/";
        redirect_page($url);
      }
      }
  }
}


// ルートにならずに、現在の位置からのhrefになっているため、エラーになっている

?>