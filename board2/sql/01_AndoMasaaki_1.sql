-- phpMyAdmin SQL Dump
-- version 4.9.5
-- https://www.phpmyadmin.net/
--
-- ホスト: localhost:3306
-- 生成日時: 2021 年 1 月 15 日 15:59
-- サーバのバージョン： 5.7.30
-- PHP のバージョン: 7.4.9

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- データベース: `gs_db2`
--

-- --------------------------------------------------------

--
-- テーブルの構造 `gs_res_data`
--

CREATE TABLE `gs_res_data` (
  `serial_id` int(11) NOT NULL COMMENT 'シリアルID',
  `thread_id` int(11) NOT NULL COMMENT 'スレッドID',
  `message_id` varchar(14) NOT NULL COMMENT 'メッセージID',
  `messageType` int(11) NOT NULL COMMENT '0:親メッセージ、1:子メッセージ',
  `userType` varchar(1) DEFAULT NULL COMMENT '1:投稿主、2:レス',
  `name` varchar(10) NOT NULL COMMENT '名前',
  `message` varchar(5000) NOT NULL COMMENT '本文',
  `pass` varchar(100) NOT NULL COMMENT '編集・削除用パスワード',
  `disabledFlg` varchar(1) NOT NULL COMMENT '無効フラグ（0:開、1:閉）',
  `deleteFlg` varchar(1) NOT NULL COMMENT '0:未削除、1:削除',
  `createTime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '登録日',
  `updateTime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '更新日'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- テーブルのデータのダンプ `gs_res_data`
--

INSERT INTO `gs_res_data` (`serial_id`, `thread_id`, `message_id`, `messageType`, `userType`, `name`, `message`, `pass`, `disabledFlg`, `deleteFlg`, `createTime`, `updateTime`) VALUES
(1, 2, '20210111100859', 0, '1', 'ジーズ太郎', 'テキストテキストテキスト1111', '03ac674216f3e15c761ee1a5e255f067953623c8b388b4459e13f978d7c846f4', '0', '0', '2021-01-11 10:08:59', '2021-01-11 23:39:33'),
(5, 2, '20210111134329', 1, '1', 'ジーズ太郎', 'aaaa1234567', '38083c7ee9121e17401883566a148aa5c2e2d55dc53bc4a94a026517dbff3c6b', '0', '0', '2021-01-11 13:43:29', '2021-01-11 19:09:01'),
(6, 2, '20210111233348', 1, '1', 'ジーズ太郎', 'テキストテキストテキスト12345', '03ac674216f3e15c761ee1a5e255f067953623c8b388b4459e13f978d7c846f4', '0', '0', '2021-01-11 23:33:48', '2021-01-11 23:34:27');

--
-- ダンプしたテーブルのインデックス
--

--
-- テーブルのインデックス `gs_res_data`
--
ALTER TABLE `gs_res_data`
  ADD PRIMARY KEY (`serial_id`);

--
-- ダンプしたテーブルのAUTO_INCREMENT
--

--
-- テーブルのAUTO_INCREMENT `gs_res_data`
--
ALTER TABLE `gs_res_data`
  MODIFY `serial_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'シリアルID', AUTO_INCREMENT=7;
