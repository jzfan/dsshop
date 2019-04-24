## Login
admin/admin123456

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
  `freeze_number` int(10) NOT NULL COMMENT '占位库存数量',
  `goods_state` tinyint(4) NOT NULL DEFAULT '0' COMMENT '商品状态：0 等待挂售；1 挂售中；2 挂售完成',
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

8.订单表增加积分、秒米支付金额
ALTER TABLE `ds_shop`.`ds_order` 
ADD COLUMN `points_amount` decimal(10, 2) NOT NULL DEFAULT 0 COMMENT '积分支付金额' AFTER `pd_amount`,
ADD COLUMN `miaomi_amount` decimal(10, 2) NOT NULL DEFAULT 0 COMMENT '秒米支付金额' AFTER `points_amount`;


9.会员挂售分账表
DROP TABLE IF EXISTS `ds_memberforsalebill`;
CREATE TABLE `ds_memberforsalebill`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_id` int(10) NOT NULL DEFAULT 0 COMMENT '订单ID',
  `order_sn` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '订单号',
  `member_id` int(10) NOT NULL DEFAULT 0 COMMENT '会员ID',
  `order_amount` decimal(10, 2) UNSIGNED NOT NULL DEFAULT 0 COMMENT '订单金额',
  `bill_percent` decimal(6, 2) UNSIGNED NOT NULL DEFAULT 0 COMMENT '分账所占订单比例',
  `bill_amount` decimal(10, 2) UNSIGNED NOT NULL DEFAULT 0 COMMENT '分账金额',
  `bill_state` tinyint(2) NOT NULL DEFAULT 0 COMMENT '分账状态',
  `created_at` datetime(0) NULL DEFAULT NULL,
  `updated_at` datetime(0) NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `member_id`(`member_id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '会员挂售分账表';

10.后台账号表
ALTER TABLE `ds_admin`
ADD COLUMN `is_shop`  tinyint(4) NOT NULL DEFAULT 1 COMMENT '1为管理员2位商户';

11.商品表
ALTER TABLE `ds_goods`
ADD COLUMN `supplier`  int(11) NOT NULL DEFAULT 0 COMMENT '供应商id';
ADD COLUMN `is_platform`  tinyint(1) NOT NULL DEFAULT 0 COMMENT '0为平台商品1为供应商商品';

12.商户配置表
DROP TABLE IF EXISTS `ds_hostconfig`;
CREATE TABLE `ds_hostconfig`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `key` varchar(255) NOT NULL  COMMENT '配置项key值',
  `value` varchar(255) NOT NULL COMMENT '配置项value值',
  `host` varchar(50) NOT NULL  COMMENT '配置的域名',
  PRIMARY KEY (`id`),
) ENGINE = InnoDB CHARACTER SET = utf8  COMMENT = '商户配置项';

13.秒米变更日志表
DROP TABLE IF EXISTS `ds_meterlog`;
CREATE TABLE `ds_meterlog` (
  `lg_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '预存款变更日志自增ID',
  `lg_member_id` int(11) NOT NULL COMMENT '会员ID',
  `lg_member_name` varchar(50) NOT NULL COMMENT '会员名称',
  `lg_admin_name` varchar(50) DEFAULT NULL COMMENT '管理员名称',
  `lg_type` varchar(15) NOT NULL DEFAULT '' COMMENT 'order_pay下单支付预存款,order_freeze下单冻结预存款,order_cancel取消订单解冻预存款,order_comb_pay下单支付被冻结的预存款,recharge充值,cash_apply申请提现冻结预存款,cash_pay提现成功,cash_del取消提现申请，解冻预存款,refund退款',
  `lg_av_amount` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '可用秒米变更0:未变更',
  `lg_addtime` int(11) NOT NULL COMMENT '变更添加时间',
  `lg_desc` varchar(150) DEFAULT NULL COMMENT '变更描述',
  `lg_freeze_amount` decimal(10,0) DEFAULT '0' COMMENT '冻结秒米',
  PRIMARY KEY (`lg_id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8 COMMENT='秒米变更日志表';


14.membe 会员表 增加meter_second,face_card,back_card字段
ALTER TABLE `ds_memeber` 
ADD COLUMN `meter_second` int(11)  NOT NULL DEFAULT 0 COMMENT '秒米' ,
ADD COLUMN `face_card` varchar(25)  DEFAULT NULL COMMENT '秒米' 身份证正面 `身份证正面`,
ADD COLUMN `back_card` varchar(35)  DEFAULT NULL COMMENT '秒米' 身份证正面 `身份证反面`,
## TODO

1. 订单超时处理
2. 秒杀挂售处理