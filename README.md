## 数据库

1. 添加数据表:
	a. seckill_goods
	b. seckill_jobs
	c. seckill_users
	
2.增加阅读积分配置	
INSERT INTO `ds_config` (`id`, `code`, `value`, `remark`) VALUES (null, 'points_read', '10', '阅读文章赠送积分')	

3.增加积分商品扩展表
DROP TABLE IF EXISTS `ds_pointgoods`;
CREATE TABLE `ds_pointgoods` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增主键',
  `goods_id` int(10) NOT NULL DEFAULT '0' COMMENT '商品ID',
  `goods_commonid` int(10) NOT NULL DEFAULT '0' COMMENT '商品公共表ID',
  `goods_type` tinyint(4) NOT NULL DEFAULT '20' COMMENT '商品类型',
  `goods_price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '商品价格',
  `goods_point` int(10) NOT NULL DEFAULT '0' COMMENT '销售所需积分',
  `goods_storage` int(10) NOT NULL DEFAULT '0' COMMENT '商品库存',
  `goods_state` tinyint(1) NOT NULL DEFAULT '1' COMMENT '商品上下架',
  PRIMARY KEY (`id`),
  KEY `goods_id` (`goods_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COMMENT='积分商品表';

4.购物车增加商品类型
ALTER TABLE `ds_cart`
ADD COLUMN `goods_type`  tinyint(4) NOT NULL DEFAULT 1 COMMENT '商品类型：1 普通商品；20 积分商品；30 91购商品' AFTER `buyer_id`;

5.91购商品表
DROP TABLE IF EXISTS `ds_forsalegoods`;
CREATE TABLE `ds_forsalegoods` (
  `id` int(11) NOT NULL,
  `goods_id` int(11) NOT NULL DEFAULT '0' COMMENT '商品ID',
  `goods_commonid` int(11) NOT NULL DEFAULT '0' COMMENT '商品公共表ID',
  `goods_type` tinyint(4) NOT NULL DEFAULT '30' COMMENT '商品类型',
  `goods_storage` int(10) NOT NULL DEFAULT '0' COMMENT '商品库存',
  `goods_price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '商品价格',
  `goods_miaomi` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '商品赠送秒米',
  `goods_state` tinyint(2) NOT NULL DEFAULT '1' COMMENT '商品状态',
  PRIMARY KEY (`id`),
  KEY `goods_id` (`goods_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='91购商品表';

6.会员挂售商品表
DROP TABLE IF EXISTS `ds_memberforsalegoods`;
CREATE TABLE `ds_memberforsalegoods` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `member_id` int(10) NOT NULL DEFAULT '0' COMMENT '会员ID',
  `goods_id` int(10) NOT NULL DEFAULT '0' COMMENT '商品ID',
  `goods_commonid` int(10) NOT NULL DEFAULT '0' COMMENT '商品公共表ID',
  `goods_price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '商品价格',
  `goods_type` tinyint(4) NOT NULL DEFAULT '30' COMMENT '商品类型',
  `goods_number` int(10) NOT NULL DEFAULT '0' COMMENT '商品数量',
  `sale_number` int(10) NOT NULL DEFAULT '0' COMMENT '已销售数量',
  `left_number` int(10) NOT NULL DEFAULT '0' COMMENT '剩余数量',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `goods_id` (`goods_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='会员挂售列表';

7.会员挂售订单表
DROP TABLE IF EXISTS `ds_memberforsaleorder`;
CREATE TABLE `ds_memberforsaleorder` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL COMMENT '订单ID',
  `order_sn` varchar(50) NOT NULL COMMENT '订单号',
  `buyer_id` int(11) NOT NULL COMMENT '购买人ID',
  `goods_number` int(11) NOT NULL COMMENT '购买商品数量',
  `goods_price` decimal(10,2) NOT NULL COMMENT '商品价格',
  `member_id` int(11) NOT NULL COMMENT '挂售人ID',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`) USING BTREE,
  KEY `member_id` (`member_id`) USING BTREE,
  KEY `order_sn` (`order_sn`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='用户挂售订单表';






## TODO

1. 计划任务
2. 代售表