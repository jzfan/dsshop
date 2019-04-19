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



## TODO

1. 计划任务
2. 代售表