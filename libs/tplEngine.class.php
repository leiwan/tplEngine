<?php
	/**
	* @file: tplEngine.class.php 类名为TPLEngine是自定义的模板引擎
	* @description: 通过该类对象加载模板文件并解析，将解析后的结果输出
	* @function： 
		assign() 向模板中分配变量;
		display() 加载模板文件，编译成新文件，并显示；
	* @author: 李晴雷
	* @version: v0.1
	* @time: 2018-6-25
	*/
	class TPLEngine {
		public 	$template_dir 		= 'templates';	// 模板存放目录，默认‘templates’
		public 	$compiles_dir 		= 'compiles';	// 编译文件存放目录，默认‘compiles’
		public	$template_postfix	= '.tpl';		// 模板文件后缀，默认‘.tpl’
		public	$compile_postfix	= '._cpl.html';	// 编译后文件后缀，默认‘._cpl.html’
		public 	$left_delimiter 	= '<{';			// 模板中变量的左界定符
		public 	$right_delimiter 	= '}>';			// 模板中变量的右界定符
		private	$tpl_vars 			= array();		// 模板中变量的数组集合
		
		/**
		* 给模板中引用的变量分配要替换成的内容
		* @param tpl_var 	string 	模板中的变量名称
		* @param value		mixed	要替换成的内容
		*/
		public function assign($tpl_var, $value = null){
			//判断tpl_var是否为空，不为空，则为其分配要替换的内容
			if($tpl_var != ''){
				$this -> tpl_vars[$tpl_var] = $value;
			}
		}
		
		/**
		* 加载模板文件，将模板中的变量替换为相应的内容，保存成新的文件，并在客户终端输出
		* @param tplFileName 	String 	模板文件名称，不带后缀
		*/
		public function display($tplFileName){
			// 模板文件路径
			$tplFileDir = $this -> template_dir.'/'.$tplFileName.($this -> template_postfix);
			// 获取模板文件内容
			$tplFileContent = file_get_contents($tplFileDir);
			// 若模板文件不存在，则退出并给出提示
			if(!file_exists($tplFileDir)){
				die("模板文件{$tplFileDir}不存在");
			}
			
			
			// 编译文件路径
			$cplFileDir = $this -> compiles_dir.'/'.$tplFileName.($this -> compile_postfix);
			// 若编译文件不存在或模板修改时间大于编译文件修改时间，则继续
			if(!file_exists($cplFileDir) || filemtime($tplFileDir) > filemtime($cplFileDir)){
				// 替换模板文件中的变量
				$rep_contents = $this -> tpl_replace($tplFileContent);
				
				// 保存编译文件
				file_put_contents($cplFileDir, $rep_contents);
			}
			
			
			// 在终端输出
			include($cplFileDir);
		}
		
		/**
		* 将模板中的变量替换为相应的内容
		* @param 	tplFileContent		String 	模板文件内容
		* @return	String 替换变量后的文本
		*/
		private function tpl_replace($tplFileContent){
			// 将左右定界符号中有影响正则的特殊符号转义  例如，<{ }>转义\<\{ \}\>
			$left = preg_quote($this -> left_delimiter, '/');
			$right = preg_quote($this -> right_delimiter, '/');
			
			/* 匹配模板中各种标识符的正则表达式数组 */
			$pattern = array(       
				/* 匹配模板中变量, 例如,"<{ $var }>"  */
				'/'.$left.'\s*\$([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)\s*'.$right.'/i',
				/* 匹配include标识符, 例如,'<{ include "header.html" }>' */
				'/'.$left.'\s*include\s+[\"\']?(.+?)[\"\']?\s*'.$right.'/i'
			);
			
			/* 替换从模板中使用正则表达式匹配到的字符串数组 */
			$replacement = array(  
				/* 替换模板中的变量 <?php echo $this->tpl_vars["var"]; */
				'<?php echo $this->tpl_vars["${1}"]; ?>',
				/* 替换include的字符串 */
				'<?php file_get_contents($this->template_dir."/${1}"); ?>'
			);
			
			/* 使用正则替换函数处理 */
			$repContent = preg_replace($pattern, $replacement, $tplFileContent); 
			
			/* 如果还有要替换的标识,递归调用自己再次替换 */
			if(preg_match('/'.$left.'([^('.$right.')]{1,})'.$right.'/', $repContent)) {       
				$repContent = $this -> tpl_replace($repContent);         	
			} 

			/* 返回替换后的字符串 */
			return $repContent;
		}
	}