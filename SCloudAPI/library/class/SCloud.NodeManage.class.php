<?php
namespace CloudSweet\SCloud;

if( !class_exists("NodeManage") ){
    class NodeManage
    {
        private $db_host;
        private $db_port;
        private $db_username;
        private $db_password;
        private $db_name;
        private $cc_encryption_hash;
        private $encryption_hash;

        public function __construct($db_host, $db_port, $db_username, $db_password, $db_name, $cc_encryption_hash, $encryption_hash)
        {
            $this->db_host = $db_host;
            $this->db_port = $db_port;
            $this->db_username = $db_username;
            $this->db_password = $db_password;
            $this->db_name = $db_name;
            $this->cc_encryption_hash = $cc_encryption_hash;
            $this->encryption_hash = $encryption_hash;
        }

        private function returnJsonResult($status, $result){
            return json_encode(array("status" => $status, "msg" => $result));
        }

        private function returnJsonSuccessResult($status, $result){
            return json_encode(array("status" => $status, "data" => $result));
        }

        /*
        action 操作 已判断
        node_action 节点操作 需判断
            node_init 根据uuid获取节点配置文件，不存在新建
            node_update 更新节点数据
                datas 加密字符串 内容：
                    uuid 节点特殊 需要判断 不存在创建并返回
                    ip 可空，不空根据uuid更新数据库
                    port 可空，不空根据uuid更新数据库
                    u 可空，不空根据uuid更新数据库
                    d 可空，不空根据uuid更新数据库
                    time 时间戳
                返回 节点配置文件

        */
        public function returnNodeManage($get)
        {
            if(!isset($get['data']) || !isset($get['uuid']))
            {
                return returnJsonResult(false, "未传入关键数据");
            }
            //后期更换加密算法
            $data = json_decode(base64_decode($get['data']), true);
            if(!is_array($data)){
                return returnJsonResult(false, "关键数据解密失败");
            }
            if(!isset($data['action'])){
                return returnJsonResult(false, "关键数据不包含操作");
            }
            switch($data['action']){
                case "node_init":
                    return returnNodeInfo($get);
                    break;
                default:
                    return returnJsonResult(false, "非法操作");
                    break;
            }
        }

        private function returnNodeInfo($get){
            $db = new \PDO('mysql:host=' . $this->db_host . ';port=' . $this->db_port . ';dbname=' . $this->db_name, $this->db_username, $this->db_password);
            $node = $db->prepare('SELECT * FROM `mod_SCloud_nodes` WHERE `uuid` = :uuid');
            $node->bindValue(':uuid', $get['uuid']);
            $node->execute();
            $node = $node->fetch();
            if (!$node)
            {
                return returnNewNode($get);
            }
            $return = array(
                "name" => $node['name'],
                "ip" => $node['ip'],
                "port" => $node['port'],
                "enabled" => $node['enabled'],
                "configoptiontable" => $node['configoptiontable'],
                "advancedconfigoptiontable" => $node['advancedconfigoptiontable'],
                "created_at" => $node['created_at'],
                "updated_at" => $node['updated_at'],
            );
            return returnJsonSuccessResult("success", $return);
        } 

        private function returnNewNode($get){
            //创建节点
            return returnJsonResult(false, "不存在的节点uuid");
        }
    }
}