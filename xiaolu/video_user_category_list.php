<?php
/**
 * @copyright: @快游戏 2014
 * @description: 获取主播专辑列表
 * @file: video_user_category_list.php
 * @author: Chen Zhong
 * @charset: UTF-8
 * @time: 2015-09-23  11:42
 * @version 1.0
 **/
include_once("../config.inc.php");
include_once("../db.config.inc.php");

/*参数*/
$mydata = array();
$mydata['pagenum'] = intval(get_param('pagenum'));//当前页
$mydata['pagenum'] = empty($mydata['pagenum']) ? 1 : intval($mydata['pagenum']);
$mydata['pagesize'] = intval(get_param('pagesize'));//每页大小
$mydata['pagesize'] = empty($mydata['pagesize']) ? 20 : intval($mydata['pagesize']);
$mydata['pid'] = intval(get_param('pid'));//分类父级id
$mydata['anchorid'] = intval(get_param('anchorid'));//主播id
$mydata['encrypt'] = get_param('encrypt');
$mydata['encrypt'] = empty($mydata['encrypt']) ? true : false;
$mydata['key'] = get_param('key'); //验证key
$request = $_SERVER['REQUEST_METHOD']; //请求方式
$key_auth = kyx_authorize_key($mydata['key'],$request);

//key判断
if(empty($key_auth) || empty($mydata['key'])){
    exit('key error');
}

$mem_obj = new kyx_memcache();

//查询条件
$where = " WHERE `vc_isshow` = 1 AND `vc_p_id` = 0 AND `vc_type_id` = 6 AND `vc_uid` = ".$mydata['anchorid'];

//查询数据总数
$data_count_key = "xl_user_category_list_count_".md5($where);
$data_count = $mem_obj->get($data_count_key);
if($data_count === false){
    $sql = "SELECT count(1) as num FROM `video_category_info` ".$where;
    $data_count = $conn->count($sql);
    $mem_obj->set($data_count_key,$data_count,3600);
}

//最大页数
$page_max = ceil($data_count/$mydata['pagesize']);

//LIMIT条件
$offset = ($mydata['pagenum'] - 1) * $mydata['pagesize'];
$limit = " LIMIT ".$offset." , ".$mydata['pagesize']." ";

//排序
$orderby = " ORDER BY `vc_order` DESC,`id` DESC ";

//定义回转的默认参数
$returnArr = array(
    'total' => $data_count, //数据总条数
    'pagecount' => $page_max, //总页数
    'pagesize' => $mydata['pagesize'], //每页显示数据
    'pagenum' => $mydata['pagenum'], //当前页
    'rows' => array() //数据数组
);

//获取游戏专辑列表
$data_key = "xl_user_category_list_data_".md5($where.$orderby.$limit);
$data = $mem_obj->get($data_key);
if($data === false){
    $sql = "SELECT `id`,`vc_name`,`vc_bicon`,`vc_game_id`,`in_date`,`vc_p_id`,`vc_intro`
            FROM `video_category_info` ".$where.$orderby.$limit;
    $data = $conn->find($sql);
    $mem_obj->set($data_key,$data,3600);
}
if(!empty($data)){
    foreach($data as $val){

        //专辑大图
        $tmp_img_url = !empty($val['vc_bicon']) ? (LOCAL_URL_DOWN_IMG.$val['vc_bicon']) : '';

        $json = array(
            'categoryid' => intval($val['id']), //分类id
            'gameid' => intval($val['vc_game_id']), //专辑关联游戏id
            'title' => filter_search(delete_html($val['vc_name'])), //视频标题
            'desc' => filter_search(delete_html($val['vc_intro'])), //专辑描述
            'pid' => intval($val['id']), //父级id
            'imgurl' => $tmp_img_url, //视频图片
            'time' => empty($val['in_date']) ? '' : date('Y-m-d',$val['in_date']) //采集时间
        );
        $returnArr['rows'][] = $json;
    }
}

$str_encode = responseJson($returnArr,$mydata['encrypt']);
exit($str_encode);

