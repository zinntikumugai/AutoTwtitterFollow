<?php
/********************************

  フォローが返しプログラム
  Make by zinntikumugai (@uesitananame)
  Base  https://syncer.jp/twitter-api-matome/

*********************************/

//設定を読み込み
include('./config.php');
//DB読み込み
include("AccessDB.php");

/*********************************
リクエストを飛ばしてくる(GET)
xxx_key & xxx_secret キーとシークレット
paramsA パラメータ(option)
request_url リクエストURL
$request_method method 指定しない場合はGET
**********************************/
function getAccsesGet($api_key, $api_secret, $access_token, $access_token_secret, $request_url, $params_a, $request_method = 'GET') {

  //method 今回はGET用
  //$request_method = 'GET';

	// キーを作成する (URLエンコードする)
	$signature_key = rawurlencode( $api_secret ) . '&' . rawurlencode( $access_token_secret ) ;

	// パラメータB (署名の材料用)
	$params_b = array(
		'oauth_token' => $access_token ,
		'oauth_consumer_key' => $api_key ,
		'oauth_signature_method' => 'HMAC-SHA1' ,
		'oauth_timestamp' => time() ,
		'oauth_nonce' => microtime() ,
		'oauth_version' => '1.0' ,
	) ;

	// パラメータAとパラメータBを合成してパラメータCを作る
	$params_c = array_merge( $params_a , $params_b ) ;

	// 連想配列をアルファベット順に並び替える
	ksort( $params_c ) ;

	// パラメータの連想配列を[キー=値&キー=値...]の文字列に変換する
	$request_params = http_build_query( $params_c , '' , '&' ) ;

	// 一部の文字列をフォロー
	$request_params = str_replace( array( '+' , '%7E' ) , array( '%20' , '~' ) , $request_params ) ;

	// 変換した文字列をURLエンコードする
	$request_params = rawurlencode( $request_params ) ;

	// リクエストメソッドをURLエンコードする
	// ここでは、URL末尾の[?]以下は付けないこと
	$encoded_request_method = rawurlencode( $request_method ) ;

	// リクエストURLをURLエンコードする
	$encoded_request_url = rawurlencode( $request_url ) ;

	// リクエストメソッド、リクエストURL、パラメータを[&]で繋ぐ
	$signature_data = $encoded_request_method . '&' . $encoded_request_url . '&' . $request_params ;

	// キー[$signature_key]とデータ[$signature_data]を利用して、HMAC-SHA1方式のハッシュ値に変換する
	$hash = hash_hmac( 'sha1' , $signature_data , $signature_key , TRUE ) ;

	// base64エンコードして、署名[$signature]が完成する
	$signature = base64_encode( $hash ) ;

	// パラメータの連想配列、[$params]に、作成した署名を加える
	$params_c['oauth_signature'] = $signature ;

	// パラメータの連想配列を[キー=値,キー=値,...]の文字列に変換する
	$header_params = http_build_query( $params_c , '' , ',' ) ;

	// リクエスト用のコンテキスト
	$context = array(
		'http' => array(
			'method' => $request_method , // リクエストメソッド
			'header' => array(			  // ヘッダー
				'Authorization: OAuth ' . $header_params ,
			) ,
		) ,
	) ;

  if( $request_method == 'GET') {
    // パラメータがある場合、URLの末尾に追加 (POSTの場合は不要)
    if( $params_a ) {
      $request_url .= '?' . http_build_query( $params_a ) ;
    }
  } else {
    // オプションがある場合、コンテキストにPOSTフィールドを作成する (GETの場合は不要)
     if( $params_a ) {
      $context['http']['content'] = http_build_query( $params_a ) ;
    }
  }

	// cURLを使ってリクエスト
	$curl = curl_init() ;
	curl_setopt( $curl , CURLOPT_URL , $request_url ) ;
	curl_setopt( $curl , CURLOPT_HEADER, 1 ) ;
	curl_setopt( $curl , CURLOPT_CUSTOMREQUEST , $context['http']['method'] ) ;			// メソッド
	curl_setopt( $curl , CURLOPT_SSL_VERIFYPEER , false ) ;								// 証明書の検証を行わない
	curl_setopt( $curl , CURLOPT_RETURNTRANSFER , true ) ;								// curl_execの結果を文字列で返す
	curl_setopt( $curl , CURLOPT_HTTPHEADER , $context['http']['header'] ) ;			// ヘッダー
  if( $request_method != 'GET') {
  	if( isset( $context['http']['content'] ) && !empty( $context['http']['content'] ) )	{	// GETの場合は不要
  		curl_setopt( $curl , CURLOPT_POSTFIELDS , $context['http']['content'] ) ;			// リクエストボディ
  	}
  }
	curl_setopt( $curl , CURLOPT_TIMEOUT , 5 ) ;										// タイムアウトの秒数
	$res1 = curl_exec( $curl ) ;
	$res2 = curl_getinfo( $curl ) ;
	curl_close( $curl ) ;

	// 取得したデータ
	$json = substr( $res1, $res2['header_size'] ) ;				// 取得したデータ(JSONなど)
	$header = substr( $res1, 0, $res2['header_size'] ) ;		// レスポンスヘッダー (検証に利用したい場合にどうぞ)

	// [cURL]ではなく、[file_get_contents()]を使うには下記の通りです…
	// $json = @file_get_contents( $request_url , false , stream_context_create( $context ) ) ;

  // JSONをオブジェクトに変換
  $obj = json_decode( $json ) ;

  //値を返す
//  return $json;
  return $obj;
}

/*************************
ユーザー名の取得 @以降の文字
*************************/
  $request_url = 'https://api.twitter.com/1.1/account/settings.json';
  $parm = array();
  $user = getAccsesGet($api_key, $api_secret, $access_token, $access_token_secret, $request_url, $parm);
//  var_dump($user);
//  echo $user->screen_name;  //オブジェクトで取得した場合
  $userName = $user->screen_name;
  echo "USERNAME =\t" .$userName ."\n";

/*************************
フォロー中のユーザーIDを取得
*************************/
  $request_url = 'https://api.twitter.com/1.1/friends/ids.json';
  $parm = array(
    'screen_name' => $userName, //ユーザー名を指定
    //'user_id' => '1528352858' ,		// ユーザーID (どちらか必須)
		//'cursor' => '599056298085224449' ,		// オフセットカーソル ?
		//'stringify_ids' => 'true' ,		// IDを文字列型で取得するか？
		//'count' => '5' ,		// 取得件数 (指定しなければ全部取得)
  );
  $friends = getAccsesGet($api_key, $api_secret, $access_token, $access_token_secret, $request_url, $parm);

/*************************
フォロワーのユーザーIDを取得
*************************/

  $request_url = 'https://api.twitter.com/1.1/followers/ids.json';
  $parm = array(
    'screen_name' => $userName, //ユーザー名を指定
    //'user_id' => '1528352858' ,		// ユーザーID (どちらか必須)
		//'cursor' => '599056298085224449' ,		// オフセットカーソル ?
		//'stringify_ids' => 'true' ,		// IDを文字列型で取得するか？
		//'count' => '5' ,		// 取得件数 (指定しなければ全部取得)
  );
  $followers = getAccsesGet($api_key, $api_secret, $access_token, $access_token_secret, $request_url, $parm);

	/*************************
	DBに登録されているユーザーIDの取得
	**************************/
	$blockLists = getBlackList( $link, $T_NAME );

  /**************************
  フォローしていないフォロワーを差分により求める
	すでにフォローリクエストを送ったものは除く
  **************************/
  $notFollowers = array_merge( array_diff($followers->ids, $friends->ids, $blockLists) );

  /**************************
  フォローされていないアカウントを差分によりもとめる
  **************************/
  $notFriends = array_merge( array_diff($friends->ids, $followers->ids) );

  /*************************
  フォローしていないフォロワーを指定人数分フォローする
  *************************/
  $cnt = 0;
  $request_url = 'https://api.twitter.com/1.1/friendships/create.json';
  /*$parm = array(
    'user_id' => '1528352858' ,		// ユーザーID (どちらか必須)
    'screen_name' => '@arayutw' ,		// スクリーンネーム (どちらか必須)
  //		'follow' => 'false' ,		// ツイート通知設定
  );*/

  foreach ($notFollowers as $value) {
    if( $cnt < $MAX_Fllow) {
      $parm = array(
        'user_id' => $value ,		// ユーザーID
      	'follow' => 'false' ,		// ツイート通知設定
      );
      echo $cnt ." ==> " .$value ."\n";
      sleep(1);
      $follow = getAccsesGet($api_key, $api_secret, $access_token, $access_token_secret, $request_url, $parm, 'POST');
      var_dump($follow);
      echo "\n\n";
      if( isset( $follow->errors) ) {
        ////////////////////////////////////
        addBlackList($link, $follow, $value, $T_NAME);
        //////////////////////////////////////
      }
      $cnt += 1;
    } else {
      break;
    }
  }
  //DBと切断
$link->close();
?>
