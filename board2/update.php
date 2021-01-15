<!DOCTYPE html>

<?php

$webRoot = $_SERVER['DOCUMENT_ROOT'];
require_once($webRoot."/board2/src/function.php");

$dbh = getDbh();

// TIMEゾーンを東京に変更
date_default_timezone_set('Asia/Tokyo');

// 変数の初期化
$page_flag = 0;
$_CLEAN = array();
$_ERROR = array();

// サニタイズ
if ( !empty($_POST) ) {
  // var_dump($_POST);
  foreach( $_POST as $key => $value) {
    if($key == "post_password"){
      $once = htmlspecialchars( $value, ENT_QUOTES);
      $_CLEAN[$key] = hash("sha256",$once);
    }else{
      $_CLEAN[$key] = htmlspecialchars( $value, ENT_QUOTES);
      // var_dump($key);
    }
  }
  // var_dump($_CLEAN);
}

if (!empty($_CLEAN["btn_confirm"])){
  $_ERROR = validation($_CLEAN);


  if( empty($_ERROR) ) {
    $page_flag = 1;
    session_start();

    // 二重送信防止用トークンの発行
    $token = uniqid('', true);

    // トークンをセッション変数にセット
    $_SESSION['token'] = $token;
  }
} else if ( !empty($_CLEAN["btn_submit"])) {
  $page_flag = 2;
  session_start();

  $id = $_GET['id'];

  // POSTされたトークンを取得
  $token = isset($_CLEAN['token']) ? $_CLEAN['token'] : "" ;

  // セッション変数のトークンを取得
  $session_token = isset($_SESSION['token']) ? $_SESSION['token'] : "";

  // セッション変数のトークンを削除
  unset($_SESSION['token']);

  // リンク用スレッドID
  $thread_id = $_POST["thread_id"];

  // 変更後メッセージ
  $message = $_POST["post_message"];

  $dbh->beginTransaction();
  // メッセージ管理テーブルへの登録
  $stmt = $dbh->prepare("UPDATE gs_res_data SET message = :message, updateTime = sysdate() WHERE message_id = :id");


  $stmt->bindValue(':id', $id, PDO::PARAM_STR);
  $stmt->bindValue(':message', $message, PDO::PARAM_STR);

  $statusMessage = $stmt->execute();

  $dbh->commit();

  if($statusMessage == false) {
    $error_sql = $stmt->errorInfo();
    exit("ErrorMessage:".$error_sql[2]);
  }

} else {
  $page_flag = 0;

  $id = $_GET['id'];

  $stmt = $dbh->prepare('SELECT * FROM gs_res_data WHERE message_id=' . $id . ';');
  $status = $stmt->execute();

  if($status == false) {
    sql_error($status);
  } else {
    $row = $stmt->fetch();
    // var_dump($row);
  }
}

function validation($_DATA){
  $dbh = getDbh();

  $id = $_GET['id'];

  $_ERROR = array();

  // コメントのバリデーション
  if( empty($_DATA["post_message"])) {
      $_ERROR[] = "メッセージを入力してください。";
  }

  // パスワードのバリデーション
  if( empty($_DATA["post_password"])) {
      $_ERROR[] = "パスワードを入力してください。";
  }

  if( !empty($_DATA["post_password"])) {
    $sql="SELECT RD.pass
          FROM gs_res_data RD
            WHERE RD.message_id = :res_id";
      $stmt = $dbh->prepare($sql);
      $stmt->bindParam(':res_id', $id, PDO::PARAM_STR);
      $stmt->execute();
      $result = $stmt->fetch(PDO::FETCH_ASSOC);
      if($_DATA["post_password"] != $result['pass']) {
        $_ERROR[] = "パスワードが違います。";
      }
  }

  return $_ERROR;
}

$title = "登録内容更新";

?>

<html lang="ja">
  <head>
    <meta charset="UTF-8">
    <title><?php print $title; ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="http://localhost/board2/assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="http://localhost/board2/assets/css/myStyle.css">
    <link rel="stylesheet" href="http://localhost/board2/assets/css/color.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    <script type="text/javascript" src="http://localhost/board2/assets/js/autosize.min.js"></script>
    <script>
    $(function(){
      autosize($('textarea'));
    });
    </script>
  </head>
<body>
  <header>
    <h1 id="logo">
      <a href="/board2/">スレッド式掲示板</a>
    </h1>
  </header>


  <?php if( $page_flag === 1 ): ?>

  <div class="container-fluid">
    <section class="container">
      <div class="row">
        <main class="col-md-9">
          <div class="new-form">
            <h2>確認画面</h2>
            <div></div>
          </div>
          <form method="POST" action="">
            <div class="form-group">
              <label>変更前メッセージ</label>
              <p class="form-control-plaintext"><?= nl2br($_CLEAN["before_message"]) ?></p>
            </div>
            <div class="form-group">
              <label>変更後メッセージ</label>
              <p class="form-control-plaintext"><?= nl2br($_CLEAN["post_message"]) ?></p>
            </div>
            <div class="text-center">
              <input type="submit" name="btn_back" class="btn btn-primary btn-margin" value="戻る">
              <input type="submit" name="btn_submit" class="btn btn-primary btn-margin" value="投稿する">
            </div>
            <input type="hidden" id="thread_id" name="thread_id" value="<?= $_CLEAN['thread_id'] ?>">
            <input type="hidden" name="post_before_message" value="<?= $_CLEAN["before_message"] ?>">
            <input type="hidden" name="post_message" value="<?= $_CLEAN["post_message"] ?>">
          </form>
        </main>
      </div>
    </section>
  </div>
  <?php elseif( $page_flag === 2 ): ?>

  <div class="container-fluid">
    <section class="container">
      <div class="row">
        <main class="col-md-9">
          <div class="new-form">
            <h2>編集完了しました</h2>
          </div>
          <div class="back-home text-center">
            <a href="/board2/thread/<?= $_POST['thread_id'] ?>/">一覧に戻る</a>
          </div>
        </main>
      </div>
    </section>
  </div>

  <?php else: ?>
    <?php if ( !empty($_ERROR)): ?>
      <ul class="error-list">
      <?php foreach( $_ERROR as $value): ?>
        <li><?= $value; ?></li>
      <?php endforeach; ?>
      </ul>
    <?php endif; ?>
    <div class="container-fluid">
      <section class="container">
        <div class="row">
          <main class="col-md-9">
            <div class="new-form">
              <h2>登録内容更新</h2>
            </div>
            <!-- エラーの場合と確認画面から戻る場合を考慮して値を保持 -->
            <section id="inputForm">
            <form method="POST" action="">
              <div class="form-group">
                <label for="post_name">名前：<?php if(!empty($_CLEAN["name"])){ echo nl2br($_CLEAN["name"]);} elseif($row["name"]){echo $row["name"];} ?></label>
              </div>
              <div class="form-group">
              <label>現在のメッセージ</label>
              <p name="now_message" class="form-control-plaintext now-message" id="now_message"><?php if(!empty($_CLEAN["before_message"])){ echo nl2br($_CLEAN["before_message"]);} elseif ($row["message"]){echo $row["message"];} ?></p>
            </div>
              <div class="form-group">
                <label>メッセージ</label>
                <textarea name="post_message" id="post_message" class="form-control" cols="30"><?php if ( !empty($_CLEAN["post_message"])){ echo $_CLEAN["post_message"]; } elseif ($row["message"]){echo $row["message"];}  ?></textarea>
                <label id="messageSupplement" class="supplement">5000文字以内</label>
              </div>
              <div class="form-group">
                <label for="post_password">編集・削除用パスワード</label>
                <input type="password" id="post_password" class="form-control"name="post_password" value="" minlength="4">
                <label id="passLabel" class="supplement">英数字4文字以上</label>
              </div>

              <div class="text-center">
              <input type="hidden" id="name" name="name" value="<?= $row['name'] ?>">
              <input type="hidden" id="thread_id" name="thread_id" value="<?php if(!empty($_CLEAN['thread_id'])){ echo $_CLEAN['thread_id'];} elseif( $row['thread_id']){echo $row['thread_id'];} ?>">
              <input type="hidden" id="before_message" name="before_message" value="<?php if ( !empty($_CLEAN['before_message'])){ echo $_CLEAN['before_message'];} elseif($row['message']){echo $row['message'];} ?>">
                <input type="submit" name="btn_confirm" class="btn btn-primary" value="確認する">
              </div>
            </form>
            </section>
            <div class="back-home text-center">
              <a href="<?php if ( !empty($_CLEAN["thread_id"])){ echo "/board2/thread/" .  $_CLEAN["thread_id"] . "/";} elseif($row["thread_id"]){echo "/board2/thread/" . $row["thread_id"] . "/";} ?>">スレッドに戻る</a>
            </div>
          </main>
        </div>
      </section>
    </div>
  <?php endif; ?>
  <footer id="footer">
  </footer>
</body>
</html>