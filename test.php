<?php
	/**
	* 示例运行入口
	*/
	
	//引用模板引擎
	include "./libs/tplEngine.class.php";
	//声明模板引擎对象
	$tplEngine = new TPLEngine;
	
	//要加载的动态数据
	$title = "模板引擎0.1版本示例";
	$content = "模板引擎0.1版本示例内容";
	
	//分配变量
	$tplEngine -> assign("title", $title);
	$tplEngine -> assign("content", $content);
	
	//在页面显示对象
	var_dump($tplEngine);
	
	//显示
	$tplEngine -> display("test");