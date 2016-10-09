# AutoTwtitterFollow
半自動的にフォローをするプログラム

##実行環境
PHP 5.3.3以上  
DB MySQL

---

##インストール
- AutoTwtitterFollowをDL、展開  
- DBを設置、`config.php`にDB設定を書き込む  
- SQL文  
```
CREATE TABLE IF NOT EXISTS `TwitterUserLockedList` (
  `id` int(20) NOT NULL AUTO_INCREMENT,
  `USERID` bigint(20) unsigned NOT NULL,
  `Code` int(10) DEFAULT NULL,
  `MESSAGE` longtext,
  `ALLString` longtext,
  `Date` datetime NOT NULL,
  `note` longtext,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Twitterの鍵垢リスト' AUTO_INCREMENT=5 ;
```
を実行
- [Application Management](https://apps.twitter.com/)からアプリケーションを登録
- Consumer、AccessのKeyとSecretを`config.php`に書き込む

定期で`Main.php`にアクセスすると自動でフォローされます。  
フォロー時に取得されるデータを吐き出すようにしているので、場合によってはMain.phpを編集してください。

##できること
- フォローしていないフォロワーをフォロー
- 鍵垢およびフォロー時にエラーが出るアカウントをDBに登録して再登録しない

##注意事項
- ノリで作成しています
- 最低限必要な機能を付けたかったので、デバックをほとんど行っていません。ご了承ください。

##参考
- [Twitter APIの使い方まとめ](https://syncer.jp/twitter-api-matome/)
