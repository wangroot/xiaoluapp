<?php
/*=============================================================================
#     FileName: tmp_action.sdk_login.in.php
#         Desc: 定期把记录在文件中的数据记录(/countapi/sdk_login_count.php)数据存在数据库存中
#       Author: cai
#        Email: ddcai@163.com
#   LastChange: 2015-03-16 14:41:48
#      History:
=============================================================================*/
include_once("../config.inc.php");
//echo('error');
//exit;
$tmp_ip = get_onlineip();//获取客户端的IP
if(!in_array($tmp_ip, $GLOBALS['SYS_AUTO_ACTION_IP'])){
	echo($tmp_ip."已记录非法IP！");
	exit;
}

include_once("../db.save.config.inc.php");

//设置memory_limit值为2048，防止因内存不足导致无法入库退出
ini_set('memory_limit','2048M');

$tmp_aa = isset($_GET["myid"])?intval($_GET["myid"]):'';
if(empty($tmp_aa)){
	echo('参数据错误！'.chr(10));
	exit;
}
//组合要入库文件的路径
$path = WEBPATH_DIR."data/sdk_login/data".date("Ymd",THIS_DATETIME - 86400)."_".$tmp_aa.".dat";
//$path = WEBPATH_DIR."data/sdk_login/data20150318_".$tmp_aa.".dat";
//echo($path);
//exit;
//随机取内容
$tmp_arr = file($path);
if(is_array($tmp_arr)){
	$tmp_table_name = "kyx_sdk_login_log";//数据表名字
	//$tmp_table_name = "kyx_sdk_login_log_back";//数据表名字
	$str_sql = "insert into ".$tmp_table_name."(`sl_in_date`, `sl_md`, `sl_bd`, `sl_dc`, `sl_sdkv`, 
			`sl_sdkbv`, `sl_vc`, `sl_vn`, `sl_pn`, `sl_title`, `sl_mac`, `sl_chl`, `sl_eid`, `sl_ct`,
			 `sl_ut`, `sl_st`, `sl_ip`,`sl_sv`,`sl_gpu`,`sl_nte`)values";
   
    $str_sql_2 = "";
    $i = 0;
    foreach($tmp_arr as $v){
        if(!empty($v)){
			$val = json_decode($v,true);
            $str_sql_2 .= "(";
            
            $tmp_sql_val = date('Ymd',floor($val['st']));//日期
            $tmp_sql_val .= ",'".$val['md']."'";//型号model
            $tmp_sql_val .= ",'".$val['bd']."'";//brand
            $tmp_sql_val .= ",'".$val['dc']."'";//手柄型号
            $tmp_sql_val .= ",".intval($val['sdkv']);//SDK版本
            $tmp_sql_val .= ",".intval($val['sdkbv']);//SDK基础版本
            $tmp_sql_val .= ",".intval($val['vc']);//游戏版本号
            $tmp_sql_val .= ",'".$val['vn']."'";//游戏版本名称
            $tmp_sql_val .= ",'".$val['pn']."'";//游戏包名
            $tmp_sql_val .= ",'".$val['title']."'";//游戏名称
            $tmp_sql_val .= ",'".$val['mac']."'";//设备MAC地址
            $tmp_sql_val .= ",'".$val['chl']."'";//渠道号
            $tmp_sql_val .= ",'".$val['eid']."'";//事件ID
            $tmp_sql_val .= ",".intval($val['ct']);//客户端记录时间 long型数据，毫秒级
            $tmp_sql_val .= ",".intval($val['ut']);//使用时间，long型数据，毫秒级
            $tmp_sql_val .= ",".$val['st'];//日志记录时间 long型数据，毫秒级
            $tmp_sql_val .= ",'".$val['ip']."'";//获取客户端的IP
            $tmp_sql_val .= ",".(isset($val['sv']) ? intval($val['sv']) : 0);//系统版本号
            $tmp_sql_val .= ",".(isset($val['gpu']) ? ("'".$val['gpu']."'") : "''")."";//gpu信息
            $tmp_sql_val .= ",".(isset($val['nte']) ? ("'".$val['nte']."'") : "''")."";//网络状态 取值：unknown（未知网络）、wifi（WiFi网络）、mobile_2、mobile_3、mobile_4
            
            $str_sql_2 .= $tmp_sql_val."),";

            if($i!=500){//每500条数据插入一次
                $i++;
            }else{
                $tmp_sql_3 = $str_sql.substr($str_sql_2,0,strlen($str_sql_2)-1);
                $conn->query($tmp_sql_3);
                $i = 0;
                $str_sql_2 = "";
            }
        }
    }

    if($str_sql_2!=""){
        $tmp_sql_3 = $str_sql.substr($str_sql_2,0,strlen($str_sql_2)-1);
        $conn->query($tmp_sql_3);
    }
    unset($tmp_arr);
    echo('导入sdk_login数据成功');
}else{
	echo('没有可导入的sdk_login数据');
}
