<?php
return array(
	//'配置项'=>'配置值'
    'DB_TYPE' => 'MYSQL',
    'DB_HOST' => '127.0.0.1',
    'DB_PORT' => '3306',
    'DB_NAME' => 'fxuser',
    'DB_USER' => 'fxuser',
    'DB_PWD'  => 'fxuser123',
    'DB_PREFIX' => 'fx_',

    'SHOW_PAGE_TRACE' => true,
    'URL_CASE_INSENSITIVE'  =>  true,
    'TMPL_L_DELIM' => '<{',
    'TMPL_R_DELIM' => '}>',

    //加载city文件
    'LOAD_EXT_CONFIG' => 'city',

    //上传配置
    'UploadConfig' => array(
        'maxSize'       =>  0, //上传的文件大小限制 (0-不做限制)
        'exts'          =>  array('jpg', 'gif', 'png', 'jpeg'),// 设置附件上传类型
        'autoSub'       =>  true, //自动子目录保存文件
        'subName'       =>  array('date', 'Ymd'), //子目录创建方式，[0]-函数名，[1]-参数，多个参数使用数组
        'rootPath'      =>  './upload/', //保存根路径
        'savePath'      =>  '', //保存路径
        'saveName'      =>  array('uniqid', ''), //上传文件命名规则，[0]-函数名，[1]-参数，多个参数使用数组
        'saveExt'       =>  '', //文件保存后缀，空则使用原后缀
        'replace'       =>  false, //存在同名是否覆盖
        'hash'          =>  true, //是否生成hash编码
        'callback'      =>  false, //检测文件是否存在回调，如果存在返回文件信息数组
        'driver'        =>  '', // 文件上传驱动
    ),

    'AdminRole' => array(
        '1' => '管理员',
        '2' => '代理',
    ),

    'GoodsStatus' => array(
        '1' => '上架',
        '2' => '下架',
    ),

    'OrderStatus' => array(
        '1' => '待付款',
        '2' => '已付款',
    ),

    'RewardStatus' => array(
        '1' => '待领取',
        '2' => '已领取',
    ),

    'PayStatus' => array(
        '1' => '待支付',
        '2' => '已支付'
    ),

);