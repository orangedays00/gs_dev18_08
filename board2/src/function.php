<?php

//XSS対応（ echoする場所で使用！それ以外はNG ）
function h($str) {
    return htmlspecialchars($str, ENT_QUOTES);
}

// 文字出力
function p($str) {
    print $str;
}

/**
 * URLのドメイン以降を取得する
 *
 * return ドメイン以降
 */
function getRequestURL() {
    return $_SERVER["REQUEST_URI"];
}

//SQLエラー関数：sql_error($stmt)
function sql_error($stmt) {
    $error = $stmt->errorInfo();
    exit("SQLError:".$error[2]);
}



//リダイレクト関数: redirect($file_name)
function redirect($page_name) {
    header("Location:detail.php?id=" . $page_name);
    exit();
}

function redirect_page($file_page) {
    header("Location:" . $file_page);
    exit();
}

/*
 * データベースハンドラーの取得
 */
function getDbh() {
    $dsn='mysql:dbname=gs_db2;charset=utf8;host=localhost';
    $user='root';
    $pass='root';
        try{
        $dbh = new PDO($dsn,$user,$pass);
        $dbh->query('SET NAMES utf8');
    }catch(PDOException $e){
        p('Error:'.$e->getMessage());
        p('データベースへの接続に失敗しました。時間をおいて再度お越し下さい。');
        die();
    }
    return $dbh;
}

/**
 * アクセスされたスレッドIDのスレッド情報を返す
*/
function getThreadInfo(){
    /** URLからスレッドIDを取得する */
    $urlArray = explode('/', getRequestURL());
    $thread_id = $urlArray[3];

    /** メッセージ取得 */
    $sql = 'SELECT
            TD.thread_id,
            TD.title,
            TD.closeFlg,
            TD.updateTime,
            count(*) count
        FROM
            gs_thread_data TD,
            gs_res_data RD
        WHERE
            TD.thread_id = RD.thread_id
        AND TD.thread_id = :thread_id
        AND TD.disabledFlg != 1
        AND RD.disabledFlg != 1';

$stmt = getDbh()->prepare($sql);
$stmt->bindParam(':thread_id', $thread_id, PDO::PARAM_STR);
$stmt->execute();
return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * レスポンスを返すフォームかどうかを返す
 *
 * return レスポンスの返信フォームならtrue,そうでないならfalse
 */
function isResponseForm(){
    return strstr(getRequestURL() ,"/thread/");
}


/**
	 * 指定されたスレッドのレスポンス件数を取得する
	 * 
	 * @param $threadId
	 * return 件数
	 */
	function getResponseCount($thread_id){
		/* スレ内のレス件数取得 */
		$sql = "SELECT count(*) FROM gs_res_data WHERE disabledFlg != 1 AND thread_id=:thread_id";
		$stmt = getDbh()->prepare($sql);
		$stmt->bindParam(':thread_id', $thread_id ,PDO::PARAM_STR);
		$stmt->execute();
		$count = $stmt->fetchColumn();
		
		return $count;
  }

/**
 * 指定されたスレッドのレスポンス情報一覧を取得する
 *
 * @param $threadId
 * return レスポンスリスト
 */
function getResponseList($thread_id){
    /* メッセージ取得 */
    $sql = "SELECT
        TD.thread_id,
        RD.serial_id,
        RD.message_id,
        RD.messageType,
        RD.userType,
        TD.title,
        RD.name,
        RD.message,
        RD.updateTime
    FROM
        gs_thread_data TD,
        gs_res_data RD
    WHERE
        TD.thread_id=RD.thread_id AND
        RD.disabledFlg != 1 AND
        TD.thread_id = :thread_id
    ORDER BY
        TD.thread_id,RD.message_id";
$stmt = getDbh()->prepare($sql);
$stmt->bindParam(':thread_id', $thread_id, PDO::PARAM_STR);
$stmt->execute();
$responseList = $stmt->fetchAll();

    return $responseList;
}

/**
 * 指定されたレスポンス情報を出力する。
 *
 * @param $r
 */
function outputResponseParent($r){
    $webRoot = (empty($_SERVER['HTTPS']) ? 'http://' : 'https://') . $_SERVER['HTTP_HOST'];
    /* 情報を変数に格納 */
    $thread_id = $r["thread_id"];
    $serial_id = $r["serial_id"];
    $res_id = $r["message_id"];
    $name = $r["name"];
    $messageType = $r["messageType"];
    $message = $r["message"];
    $updateTime = $r["updateTime"];
    $userType = $r["userType"];

    p('
    <div class="response">
    <div class="res-header">
    <p class="name">'.$name.'</p>
    <p style="text-align:right;">'.date('Y/m/d(D) H:i', strtotime($updateTime)).'</p>
    </div>
    <hr>
    
    <p class="message">'.nl2br($message).'</p>
    <div class="action-form-group">
    <button type="button" class="btn" id="editSend"><a href="' . $webRoot . '/board2/update.php?id=' . $res_id . '">編集する</a></button>
    </div>
    </div>
    ');
}

function outputResponseChild($r){
    $webRoot = (empty($_SERVER['HTTPS']) ? 'http://' : 'https://') . $_SERVER['HTTP_HOST'];
    /* 情報を変数に格納 */
    $thread_id = $r["thread_id"];
    $serial_id = $r["serial_id"];
    $res_id = $r["message_id"];
    $name = $r["name"];
    $messageType = $r["messageType"];
    $message = $r["message"];
    $updateTime = $r["updateTime"];
    $userType = $r["userType"];

    p('
    <div class="response">
    <div class="res-header">
    <p class="name">'.$name.'</p>
    <p style="text-align:right;">'.date('Y/m/d(D) H:i', strtotime($updateTime)).'</p>
    </div>
    <hr>
    
    <p class="message">'.nl2br($message).'</p>
    <div class="action-form-group">
    <button type="button" class="btn" id="editSend"><a href="' . $webRoot . '/board2/update.php?id=' . $res_id . '">編集する</a></button>
    <form method="POST" action="' . $webRoot . '/board2/src/delete.php?id=' . $res_id . '">
    <div class="form-delete-group">
    <input type="hidden" class="form-control" name="thread_id" value="' . $thread_id . '">
    <input type="password" class="delete-pass form-control" name="post_password" value="">
        <input type="submit" class="btn" name="deleteSend" value="削除する">
    </div>
    </form>
    <label id="deletepassLabel" class="supplement">登録した編集・削除用パスワードを入力してください。</label>
    <div id="'. $res_id .'"></div>
    </div>
    </div>
    ');
}

/**
* スレッドをすべて取得する
*/
function getFullThread(){
    /* 取得クエリ */
    $sql = "SELECT thread_id ,title ,updateTime
        FROM gs_thread_data
        WHERE disabledFlg != 1
        ORDER BY updateTime DESC,thread_id";

    /* 取得準備 */
    $stmt = getDbh()->prepare($sql);
    /* スレッド情報を取得 */
    $stmt->execute();
    /** 全件を$resultに代入 */
    $result = $stmt->fetchAll();
    /** 呼び出しもとに取得結果を返す */
    return $result;
}

/**
 * スレッド情報の出力
 */
function outThreadList($result){

    /* 最新レスポンス情報を取得 */
    $first = getThreadFirst($result["thread_id"]);
    // /* 件数を取得 */
    // $count = getResponseCount($result["thread_id"]);

    p('
        <div class="thread">
        <a href="/board2/thread/'.$result["thread_id"].'/" class="transmission">
    ');

    // if(isToday($first["updateTime"])){
    //     p("<span class=\"new option2\">new</span>");
    // }

    p('
        <h4>'.$result["title"].'</h4>
        <p>'.getTrimString($first["message"],'50').'</p>
        </a>
        </div>
    ');
}

function getTrimString($string, $trimLength){
    $count = mb_strlen($string);
    $string = mb_substr($string ,0 ,$trimLength);
    if($count > $trimLength){ $string = $string.'...'; }
    return $string;
    }


/**
 * 指定されたスレッドの最新のレスポンス情報を取得する
 */
function getThreadFirst($thread_id){
    /* 最新レス内容取得 */
    $sql = "SELECT
        TD.thread_id,
        RD.serial_id,
        RD.message_id,
        RD.messageType,
        RD.userType,
        TD.title,
        RD.name,
        RD.message,
        RD.userType,
        RD.createTime,
        TD.updateTime
    FROM
        gs_thread_data TD,
        gs_res_data RD
    WHERE
        TD.thread_id=RD.thread_id AND
        RD.disabledFlg != 1 AND
        TD.thread_id = :thread_id
    ORDER BY
        RD.message_id ASC LIMIT 1";

$stmt = getDbh()->prepare($sql);
$stmt->bindParam(':thread_id', $thread_id ,PDO::PARAM_STR);
$stmt->execute();
$result = $stmt->fetch(PDO::FETCH_ASSOC);

return $result;
}

function getSearchList($search) {
    $sql = "SELECT TD.thread_id, TD.title, TD.updateTime
            FROM gs_thread_data TD,
                 gs_res_data RD
            WHERE TD.thread_id = RD.thread_id AND
                    TD.disabledFlg != 1 AND
                    RD.message LIKE '%" . $search . "%'
            GROUP BY TD.thread_id
            ORDER BY TD.updateTime DESC, TD.thread_id";
    $stmt = getDbh()->prepare($sql);
    $stmt->execute();
    $searchResult = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $searchResult;
}

function change($changeId) {
    return $changeId = $changeId;
}

?>