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

    'Wx' => array(
        'AppID' => 'wxc3d04f676c17c0ba',
        'AppSecret' => 'b4bf70c90f7975e72d78b7803697e434',
        'Token' => 'Z60z6Z6Q1aavK30K0GVv460t30bnA606',       //微信Token(令牌)
        'EncodingAESKey' => 'HdJJKSjx0kqcheREd1zYqJnSy4OCcRHeKdJyj2hECSH',//微信消息加解密密钥
        'key' => '123456789012345678901234567890rz',
        'mch_id' => '1267553601', //商户号
        'notify_url' => 'http://' . $_SERVER['HTTP_HOST'] . '/index.php/wechat/notify',
        'SSLCERT_PATH' => LIB_PATH . "Org/Wxpay/apiclient_cert.pem",
        'SSLKEY_PATH' => LIB_PATH . "Org/Wxpay/apiclient_key.pem",
        'CURL_PROXY_HOST' => "0.0.0.0",
        'CURL_PROXY_PORT' => 0,
        'REPORT_LEVENL' => 1,
    ),

    'Wechat' => array(
        'welcome' => '欢迎关注我们',	//公众号自动回复消息
    ),

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

    'RewardType' => array(
        '1' => '返利红包',
        '2' => '直接红包',
        '3' => '间接红包',
        '4' => '团队红包',
        '5' => '升级代理红包'
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