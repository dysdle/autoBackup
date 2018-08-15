<?

    /* 数据库表自动备份（部分代码）
     * @access public
     * @author dylan <41303942@qq.com> 2018-08-15 16:21:45
     *
     * */
    public function autoBackup(){
        set_time_limit(0);
        $log = "/*  \nAuthor:dylan \nemail:413036942@qq.com \nDate : ".date("Y-m-d H:i:s",time())."\n"."*/\n";
        //获取所有数据库表
        $get_db_table_sql = "SHOW TABLE STATUS" ;
        $db_tables =  M()->query($get_db_table_sql);
        foreach ($db_tables as $k =>$v){
            //获取表字段及属性
            $get_table_fields_sql = "SHOW FULL COLUMNS FROM ".$v['Name'];
            $get_table_fields = M()->query($get_table_fields_sql);
            /*创建表过程*/
            $end1 = $end2 = $str = '';
            $str = $log.$str;
            $str .= "SET FOREIGN_KEY_CHECKS=0 ;\n";
            $str .="-- ----------------------------\n-- Table structure for ".$v['Name']."\n-- ----------------------------\n";
            $str .="DROP TABLE IF EXISTS `".$v['Name']."`;\n";
            $str .= "CREATE TABLE `".$v['Name']."` (";
            foreach ($get_table_fields as $kk=>$vv){
                $str.= "`".$vv['Field']."` ".$vv['Type'];
                if($vv['Null']=='NO'){
                    $str .= ' NOT NULL ';
                }
                if($vv['Default']=='CURRENT_TIMESTAMP'){
                    $str .= " DEFAULT CURRENT_TIMESTAMP ";
                }elseif($vv['Default']!=''){
                    $str .= " DEFAULT '".$vv['Default']."' ";
                }else{
                    if($vv['Key']!='PRI'){
                        $str .= " DEFAULT "."'' ";
                    }
                }
                $str.= $vv['Extra']." COMMENT '".$vv['Comment']."',";
                if($vv['Key']=='PRI'){
                    //主键信息
                    $end1.= 'PRIMARY KEY (`'.$vv['Field'].'`),';
                }
                if($vv['Key'] == 'MUL'){
                    //索引信息
                    $end2 .= 'KEY `'.$vv['Field'].'` (`'.$vv['Field'].'`),';
                }
            }
            $end2 =  substr($end2,0,strlen($end2)-1);
            //去掉最后一个字符
            $str = $str.$end1.$end2;
            $str .= ") ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 COMMENT='".$v['Comment']."';";
            $str .= "\n\n\n";
            $str .="-- ----------------------------\n-- Records of ".$v['Name']."\n-- ----------------------------\n";
            /*备份表数据过程*/
            $get_data_sql = "select * from ".$v['Name'];
            $data = M()->query($get_data_sql);
            foreach($data as $kkk=>$vvv){
                $str .= "INSERT INTO `".$v['Name']."` VALUES (";
                foreach ($get_table_fields as $kk=>$vv){
                    $str .= "'".$vvv[$vv['Field']]."',";
                }
                $str = substr($str,0,strlen($str)-1);
                $str.= ");\n";
            }
            /*写文件操作*/
            if (!is_dir(ROOT_PATH.'/backup/')) mkdir(ROOT_PATH.'/backup/', 0777);
            @file_put_contents(ROOT_PATH."/backup/".$v['Name']."_".date("YmdHis").".sql",$str);
        }
    }


