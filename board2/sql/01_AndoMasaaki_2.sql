-- phpMyAdmin SQL Dump
-- version 4.9.5
-- https://www.phpmyadmin.net/
--
-- ホスト: localhost:3306
-- 生成日時: 2021 年 1 月 15 日 16:01
-- サーバのバージョン： 5.7.30
-- PHP のバージョン: 7.4.9

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- データベース: `gs_db2`
--

-- --------------------------------------------------------

--
-- テーブルの構造 `gs_thread_data`
--

CREATE TABLE `gs_thread_data` (
  `thread_id` int(11) NOT NULL COMMENT 'スレッドID',
  `title` varchar(30) NOT NULL COMMENT 'スレッドタイトル',
  `pass` varchar(100) NOT NULL COMMENT 'パスワード',
  `closeFlg` varchar(1) NOT NULL COMMENT '閉じフラグ（0:開、1:閉）',
  `disabledFlg` varchar(1) NOT NULL COMMENT '無効フラグ（0:開、1:閉）	',
  `createTime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '登録日',
  `updateTime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '更新日'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- テーブルのデータのダンプ `gs_thread_data`
--

INSERT INTO `gs_thread_data` (`thread_id`, `title`, `pass`, `closeFlg`, `disabledFlg`, `createTime`, `updateTime`) VALUES
(2, 'タイトルテスト', '03ac674216f3e15c761ee1a5e255f067953623c8b388b4459e13f978d7c846f4', '0', '0', '2021-01-11 10:08:59', '2021-01-11 23:33:48');

--
-- ダンプしたテーブルのインデックス
--

--
-- テーブルのインデックス `gs_thread_data`
--
ALTER TABLE `gs_thread_data`
  ADD PRIMARY KEY (`thread_id`);

--
-- ダンプしたテーブルのAUTO_INCREMENT
--

--
-- テーブルのAUTO_INCREMENT `gs_thread_data`
--
ALTER TABLE `gs_thread_data`
  MODIFY `thread_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'スレッドID', AUTO_INCREMENT=3;
