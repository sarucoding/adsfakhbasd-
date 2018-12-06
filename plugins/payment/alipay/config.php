<?php
return array(
		'code'=> 'alipay',
		'name' => '支付宝支付',
		'version' => '1.0',
		'author' => 'wushunjun',
		'desc' => 'PC端支付宝插件 ',
		'scene' =>2,  // 使用场景 0 PC+手机 1 手机 2 PC
		'icon' => 'logo.jpg',
		'config' => array(
				array('name' => 'alipay_account','label'=>'支付宝帐户', 'description' => '支付宝登陆账号','type' => 'text',  'value' => ''),
				array('name' => 'alipay_key','label'=>'交易安全校验码', 'description' => '','type' => 'text',   'value' => ''),
				array('name' => 'alipay_partner','label'=>'合作者身份ID', 'description' => '','type' => 'text', 'value' => ''),
				array('name' => 'alipay_account_name','label'=>'付款账号名','description' => '付款方的支付宝账户名','type' => 'text',  'value' => ''),
				array('name' => 'alipay_private_key','label'=>'密钥', 'description' => 'APP端所需秘钥,PC端无需配置',  'type' => 'textarea',   'value' => ''),
				array('name' => 'alipay_pay_method','label'=>' 选择接口类型', 'description' => '','type' => 'select', 'option' => array(
						'1' =>  '使用担保交易接口',
						'2' => '使用即时到帐交易接口',
				)),
				array('name' => 'is_bank','label'=>'是否开通网银',        'type' => 'select', 'option' => array(
						'0' => '否',
						'1' =>  '是',
				)),
		),
		'bank_code'=>array(
				'招商银行'=>'CMB-DEBIT',
				'中国工商银行'=>'ICBC-DEBIT',
				'交通银行'=>'COMM-DEBIT',
				'中国建设银行'=>'CCB-DEBIT',
				'中国民生银行'=>'CMBC',
				'中国银行'=>'BOC-DEBIT',
				'中国农业银行'=>'ABC',
				'上海银行'=>'SHBANK',
		)
);
/*$config = array (
		//应用ID,您的APPID。
		'app_id' => "2016120203742686",

		//商户私钥，您的原始格式RSA私钥
		'merchant_private_key' => "MIIEogIBAAKCAQEAra+I+rgVhzSMW4lXfQssnig3aVnmfLctq25uYt8VMAd53dhS2YaCe9SHMLfgsgF+jqjO4yt9yFORVHxwsaNBfK1JOwIS31ZrSwW8epL2HZArWgNinRFtUPNz5XcYOjkwSs0E3fCrN86Kh62m2UOlbgI+/ZMy/5L2CX+dNQ+/lQFBCuIufusC2hIt5YziVK2/6PDVyyx5iNyU5wDpDV2rep06zvszw55lA731c5rFZZZWORqak4y5yE34aeEhui/c83NAnu/kVYHwBr8zmwnfuUevHIEQ0mXGV+jlCo7TvXFGakBzW901tm5OgxiY+f4DV+fThOTohnBvMH3eEZCmjwIDAQABAoIBAA9XixUCf7xSsvc++YBtJULPMJ3YuBhaIembtpp7NCbq8iPbgO4ACLShgFOYWnu+0AwP8z5z0AeLAjJhT58RWa6GrApPU5Vwz8dvuUdmheD0pC+uTt6q9GoLJzikvXofJRnmZECqiqVCVFBJ0YwtgoZmImRJxV53/820/RD71CN4NIz6kL37dreyiUeQl41gcJ4Todxrpp9b4xBBzZ/cLmzOdmcGM7pP3XHaspiK2o0GzFtE6aESTZ84/WIEiTZLCBgrwHIvNP7bWrbB+yN504z3Vg4Y1FEik7DgZ8Zbeqmr7Wg8qZDedsq9yBdvJ+T+04/tq5jn7wg83/h62tyGu4kCgYEA4a4fGR7w6pw3waSFZ+zVJFZQDyLYUFW2RCFK6kpyaD85f4YTYd9kN4avm07FWnfJDs8Wl4CWYFnJ4xBQF/YZYRibCT4CQTgPYh30IP980UEX00UAesW9g1j+VLWh8QEc2KXSo0GFOvmdir/nq00WPkeKOZzaZVk4R2ZlAmaKo7MCgYEAxQUnTWyG3a9QrEhgLpEPqgKszp14xlHF2V3488PbMo+XxXp+5Hx06j1Tfr+5Be5+dXIvT0TY9JUu11m/PWFfvgBwnyAz8R6U7wP1fGkBzQfHtymr57qKxgFBeZy69VisB1ej56ZN6XAQt4DkRwp9s4TKla4K9RWb6RyIxLM487UCgYBUMQIW/A0CaS7/xaGjKJ5HRQ/u5Z/vMFqjxgvNpeJSc6u+oEUg0RbxBAUFGnjTjDZsmOMjt+vhm/2OAOrwVXYYW/aSlxh+Piy2/Neza7yjz/XUcjyAkL5nfs66yVvVrgWV6R7QmsPaQX79YSRLKqHur/+oCJUNFCDDujZak+iKswKBgFXKBbAf9qXnJfCu3zrHrZNw8MBTL3jjuIwK8FFs0jC09/hke4aQbvRkWcwSPcXIcMZBPzp8FyCBKVFaYfyfPupkFKYlhpiSoXVmOum+a6tUnPEzswgHYVAQ0erbhUk6IEZeMh+3eauRQaY1+LC6b7vQscn1bA4GJ8qcwy0rgTwVAoGADGP2VqitGESvhHEagoMmTGM5E3VOBKz/Skmdc/237KDc9/hWHHnTdXhdMs4JSYyup0KwbxQKT725eQGTVh2SjuRA4CRiVEaoimJqx/5usHfh+csSy1y7jW/E2P63eBbbHepcrtxcb2SEdERmGnzHeZRaU9owM8dO3tTODqjzkug=",
		
		//异步通知地址
		'notify_url' => "http://工程公网访问地址/alipay.trade.wap.pay-PHP-UTF-8/notify_url.php",
		
		//同步跳转
		'return_url' => "http://mitsein.com/alipay.trade.wap.pay-PHP-UTF-8/return_url.php",

		//编码格式
		'charset' => "UTF-8",

		//签名方式
		'sign_type'=>"RSA2",

		//支付宝网关
		'gatewayUrl' => "https://openapi.alipay.com/gateway.do",

		//支付宝公钥,查看地址：https://openhome.alipay.com/platform/keyManage.htm 对应APPID下的支付宝公钥。
		'alipay_public_key' => "MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAra+I+rgVhzSMW4lXfQssnig3aVnmfLctq25uYt8VMAd53dhS2YaCe9SHMLfgsgF+jqjO4yt9yFORVHxwsaNBfK1JOwIS31ZrSwW8epL2HZArWgNinRFtUPNz5XcYOjkwSs0E3fCrN86Kh62m2UOlbgI+/ZMy/5L2CX+dNQ+/lQFBCuIufusC2hIt5YziVK2/6PDVyyx5iNyU5wDpDV2rep06zvszw55lA731c5rFZZZWORqak4y5yE34aeEhui/c83NAnu/kVYHwBr8zmwnfuUevHIEQ0mXGV+jlCo7TvXFGakBzW901tm5OgxiY+f4DV+fThOTohnBvMH3eEZCmjwIDAQAB",
		
	
);*/