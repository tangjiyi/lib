## MySQL自定义变量

#### 用户变量介绍

```sql
set @ver:=0
select count(1) into @ver from table
select @ver
```

#### 下面说一点细节可以加深我和自定义变量之间的合作方式

当我select 想要的列时候(select id from table)可以看成是读取了一列id，也可以看成是一行一行读取id列，每取一条记录，游标往下走一格，当遍历完所有的数据之后再呈现给我们一列id。按照第二种方式相当于是python遍历数组，中间自然可以加一些变量来存储一些数据。

有点抽象?举个例子~

某个数据表格记录了AB两个店每个小时的营业额，表结构如下

```sql
CREATE TABLE `wk_test` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `date` varchar(20) NOT NULL COMMENT '日期',
  `shop` varchar(255) NOT NULL COMMENT '商店',
  `hour` int NOT NULL COMMENT '小时',
  `income` int NOT NULL COMMENT '收入',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8;
```

插入测试数据
```sql
INSERT INTO `wk_test` ( `date`, `shop`, `hour`, `income` )
VALUES
	( '2021-01-01', 'A', 0, 19 ),
	( '2021-01-01', 'A', 1, 10 ),
	( '2021-01-01', 'A', 2, 21 ),
	( '2021-01-01', 'A', 3, 22 ),
	( '2021-01-01', 'A', 4, 13 ),
	( '2021-01-01', 'A', 5, 24 ),
	( '2021-01-01', 'A', 6, 25 ),
	( '2021-01-01', 'A', 7, 43 ),
	( '2021-01-01', 'A', 8, 11 ),
	( '2021-01-01', 'B', 0, 21 ),
	( '2021-01-01', 'B', 1, 13 ),
	( '2021-01-01', 'B', 2, 22 ),
	( '2021-01-01', 'B', 3, 24 ),
	( '2021-01-01', 'B', 4, 23 ),
	( '2021-01-01', 'B', 5, 41 ),
	( '2021-01-01', 'B', 6, 65 ),
	( '2021-01-01', 'B', 7, 63 ),
	( '2021-01-01', 'B', 8, 41 );
```

如何观察每天某个时间点A,B两个店的分别的累计营业额?
按照上面所说的原理，如果每读出一条记录相当于游标往下走一下，我们在遍历数据的过程中用变量对中间结果进行记录和判断，那么就能实现上述需求，代码如下：

```sql
SET @cosum := 0;
SET @dates := '';
SET @shop := '';

SELECT
	date,
	shop,
	HOUR,
	income,
	@group_income :=
CASE
		-- 判断是否是同一天的同一个商店
		WHEN @dates = a.date 
		AND @shop = a.shop
	THEN
			 -- 是一个则累加
			@cosum := @cosum + income
			-- 不是则将第一个小时的值赋值给累加量
			ELSE @cosum := a.income 
		END AS group_income,
		-- 保存当前用于判断的变量
		@dates := a.date,
		@shop := a.shop 
FROM
	( SELECT * FROM wk_test ORDER BY date, shop, HOUR ) a
```
