-- phpMyAdmin SQL Dump
-- version 4.6.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: 2016-06-15 23:17:32
-- 服务器版本： 5.5.40
-- PHP Version: 5.6.1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Database: `fxuser`
--

-- --------------------------------------------------------

--
-- 表的结构 `fx_admin`
--

CREATE TABLE `fx_admin` (
  `aid` int(10) UNSIGNED NOT NULL,
  `name` varchar(45) NOT NULL,
  `password` varchar(45) NOT NULL COMMENT '密码',
  `role` tinyint(8) UNSIGNED NOT NULL COMMENT '角色',
  `city` text COMMENT '代理的代理城市代码',
  `time` int(10) UNSIGNED NOT NULL COMMENT '添加时间'
) ENGINE=MyISAM ;

-- --------------------------------------------------------

--
-- 表的结构 `fx_goods`
--

CREATE TABLE `fx_goods` (
  `gid` int(10) UNSIGNED NOT NULL,
  `title` varchar(45) NOT NULL,
  `name` varchar(45) NOT NULL,
  `price` decimal(10,2) UNSIGNED NOT NULL COMMENT '价格',
  `self` decimal(10,2) DEFAULT NULL COMMENT '自己红包',
  `up1` decimal(10,2) UNSIGNED NOT NULL COMMENT '一级红包',
  `up2` decimal(10,2) UNSIGNED NOT NULL COMMENT '二级红包',
  `leader` decimal(10,2) UNSIGNED NOT NULL,
  `img` varchar(255) NOT NULL,
  `desc` text,
  `status` tinyint(8) UNSIGNED NOT NULL COMMENT '状态',
  `openid` varchar(45) NOT NULL
) ENGINE=MyISAM ;

-- --------------------------------------------------------

--
-- 表的结构 `fx_order`
--

CREATE TABLE `fx_order` (
  `oid` int(10) UNSIGNED NOT NULL,
  `uid` int(10) UNSIGNED NOT NULL,
  `gid` int(10) UNSIGNED NOT NULL,
  `time` int(10) UNSIGNED NOT NULL,
  `name` varchar(45) DEFAULT NULL,
  `tel` varchar(45) DEFAULT NULL,
  `addr` varchar(45) DEFAULT NULL,
  `sheng` int(10) UNSIGNED NOT NULL,
  `shi` int(10) UNSIGNED NOT NULL,
  `city` int(10) UNSIGNED NOT NULL COMMENT '城市代码',
  `money` decimal(10,2) UNSIGNED NOT NULL COMMENT '购买价',
  `status` tinyint(8) UNSIGNED NOT NULL COMMENT '支付状态'
) ENGINE=MyISAM ;

-- --------------------------------------------------------

--
-- 表的结构 `fx_pack`
--

CREATE TABLE `fx_pack` (
  `pid` int(10) UNSIGNED NOT NULL,
  `trade` varchar(45) DEFAULT NULL COMMENT '商户订单号',
  `uid` int(10) UNSIGNED NOT NULL COMMENT '用户id',
  `money` decimal(10,2) DEFAULT NULL COMMENT '红包金额',
  `time` datetime DEFAULT NULL COMMENT '发放时间'
) ENGINE=MyISAM  COMMENT='微信支付提现红包';

-- --------------------------------------------------------

--
-- 表的结构 `fx_pay`
--

CREATE TABLE `fx_pay` (
  `pid` bigint(20) NOT NULL,
  `oid` int(10) NOT NULL COMMENT '订单id',
  `create_time` datetime NOT NULL COMMENT '创建时间',
  `pay_time` datetime DEFAULT NULL COMMENT '支付时间',
  `status` tinyint(8) DEFAULT NULL COMMENT '支付状态',
  `money` decimal(10,2) UNSIGNED NOT NULL COMMENT '金额',
  `uid` int(10) UNSIGNED NOT NULL COMMENT '支付用户'
) ENGINE=InnoDB ;

-- --------------------------------------------------------

--
-- 表的结构 `fx_reward`
--

CREATE TABLE `fx_reward` (
  `rid` int(10) UNSIGNED NOT NULL,
  `money` decimal(10,2) UNSIGNED NOT NULL COMMENT '红包金额',
  `note` varchar(255) DEFAULT NULL COMMENT '备注',
  `type` tinyint(8) UNSIGNED NOT NULL DEFAULT '0' COMMENT '红包类型',
  `uid` int(10) UNSIGNED NOT NULL COMMENT '领取人id',
  `time` int(10) UNSIGNED NOT NULL COMMENT '发放时间',
  `status` tinyint(8) UNSIGNED NOT NULL COMMENT '领取状态',
  `price` decimal(10,2) UNSIGNED NOT NULL DEFAULT '0.00' COMMENT '领取红包的最低级别'
) ENGINE=MyISAM  COMMENT='回报表';

-- --------------------------------------------------------

--
-- 表的结构 `fx_user`
--

CREATE TABLE `fx_user` (
  `uid` int(10) UNSIGNED NOT NULL,
  `nickname` varchar(45) NOT NULL,
  `openid` varchar(45) DEFAULT NULL,
  `headimgurl` varchar(255) DEFAULT NULL,
  `money` decimal(10,2) UNSIGNED NOT NULL DEFAULT '0.00' COMMENT '账户余额',
  `up1` int(10) UNSIGNED NOT NULL COMMENT '一级上属id',
  `up2` int(10) UNSIGNED NOT NULL COMMENT '二级上属id',
  `leader` int(10) UNSIGNED NOT NULL COMMENT '团队领导人id',
  `agent` tinyint(8) UNSIGNED NOT NULL DEFAULT '0' COMMENT '是否是代理，默认不是0',
  `vip` decimal(10,2) UNSIGNED NOT NULL COMMENT '自己的级别'
) ENGINE=MyISAM ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `fx_admin`
--
ALTER TABLE `fx_admin`
  ADD PRIMARY KEY (`aid`);

--
-- Indexes for table `fx_goods`
--
ALTER TABLE `fx_goods`
  ADD PRIMARY KEY (`gid`);

--
-- Indexes for table `fx_order`
--
ALTER TABLE `fx_order`
  ADD PRIMARY KEY (`oid`);

--
-- Indexes for table `fx_pack`
--
ALTER TABLE `fx_pack`
  ADD PRIMARY KEY (`pid`);

--
-- Indexes for table `fx_pay`
--
ALTER TABLE `fx_pay`
  ADD PRIMARY KEY (`pid`);

--
-- Indexes for table `fx_reward`
--
ALTER TABLE `fx_reward`
  ADD PRIMARY KEY (`rid`);

--
-- Indexes for table `fx_user`
--
ALTER TABLE `fx_user`
  ADD PRIMARY KEY (`uid`),
  ADD KEY `openid` (`openid`);

--
-- 在导出的表使用AUTO_INCREMENT
--

--
-- 使用表AUTO_INCREMENT `fx_admin`
--
ALTER TABLE `fx_admin`
  MODIFY `aid` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
--
-- 使用表AUTO_INCREMENT `fx_goods`
--
ALTER TABLE `fx_goods`
  MODIFY `gid` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;
--
-- 使用表AUTO_INCREMENT `fx_order`
--
ALTER TABLE `fx_order`
  MODIFY `oid` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=93;
--
-- 使用表AUTO_INCREMENT `fx_pack`
--
ALTER TABLE `fx_pack`
  MODIFY `pid` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- 使用表AUTO_INCREMENT `fx_reward`
--
ALTER TABLE `fx_reward`
  MODIFY `rid` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=160;
--
-- 使用表AUTO_INCREMENT `fx_user`
--
ALTER TABLE `fx_user`
  MODIFY `uid` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1810;