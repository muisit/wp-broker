<?php

/**
 * Wp-Broker API Interface
 * 
 * @package             wp-broker
 * @author              Michiel Uitdehaag
 * @copyright           2020 Michiel Uitdehaag for muis IT
 * @licenses            GPL-3.0-or-later
 *
 * This file is part of wp-broker.
 *
 * wp-broker is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * wp-broker is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with wp-broker.  If not, see <https://www.gnu.org/licenses/>.
 */


 namespace WPBroker;

 class API {
    private $supported_models = array(
        "wall"
    );

    public function createNonceText() {
        $user = wp_get_current_user();        
        if(!empty($user)) {
            return "wpbroker".$user->ID;
        }
        return "wpbroker";
    }

    public function authenticate($nonce) {
        $result = wp_verify_nonce( $nonce, $this->createNonceText() );
        if(!($result === 1 || $result === 2)) {
            error_log('die because nonce does not match');
            die(403);
        }

        if( ! current_user_can( 'has_wall' ) ) {
            error_log("unauthenticated");
            die(403);
        }
    }

    public function resolve() {
        $json = file_get_contents('php://input');
        $data = json_decode($json,true);

        if(empty($data) || !isset($data['path'])) {
            error_log('die because no path');
            die(403);
        }

        $modeldata = isset($data['model']) ? $data['model'] : array();

        $offset = isset($modeldata['offset']) ? intval($modeldata['offset']) : 0;
        $pagesize = isset($modeldata['pagesize']) ? intval($modeldata['pagesize']) : 20;
        $filter = isset($modeldata['filter']) ? $modeldata['filter'] : array();
        $sort = isset($modeldata['sort']) ? $modeldata['sort'] : "";
        $special = isset($modeldata['special']) ? $modeldata['special'] : "";
        $nonce = isset($data['nonce']) ? $data['nonce'] : null;

        $path=$data['path'];
        if(empty($path)) {
            $path="index";
        }
        $path=explode('/',trim($path,'/'));
        if(!is_array($path) || sizeof($path) == 0) {
            $path=array("index");
        }

        $retval=array();

        if(in_array($path[0],$this->supported_models)) {
            $model = $this->loadModel(ucfirst($path[0]));

            if(sizeof($path)>1) {
                switch($path[1]) {
                case 'save':
                    $retval = array_merge($retval, $this->save($model, $modeldata));
                    break;
                case 'delete':
                    $retval = array_merge($retval, $this->delete($model, $modeldata));
                    break;
                case 'get':
                    $retval = array_merge($retval, $this->find($model, $modeldata));
                    break;
                default:
                    if(method_exists($model,$path[1])) {
                        $method=$path[1];
                        $retval = $model->$method($modeldata);
                    }
                    else {
                        die(403);
                    }
                    break;
                }
            }
            else {
                $retval = array_merge($retval, $this->listAll($model, $offset, $pagesize, $filter, $sort, $special));
            }
        }
        else {
            die(403);
        }

        if(!isset($retval["error"])) {
            wp_send_json_success($retval);
        }
        else {
            wp_send_json_error($retval);
        }
        wp_die();
    }

    private function save($model, $data) {
        $retval=array();
        if(!$model->saveFromObject($data)) {
            $retval["error"]=true;
            $retval["messages"]=$model->errors;
        }
        else {
            $retval["id"] = $model->{$model->pk};
        }
        return $retval;
    }

    private function delete($model, $data) {
        $retval=array();
        if(!$model->delete($data['id'])) {
            $retval["error"]=true;
            $retval["messages"]=array("Internal database error");
            if(isset($model->errors) && is_array($model->errors)) {
                $retval["messages"]=$model->errors;
            }
        }
        else {
            $retval["id"] = $model->{$model->pk};
        }
        return $retval;
    }

    private function listAll($model,$offset,$pagesize,$filter,$sort,$special) {
        return $this->listResults($model, $model->selectAll($offset,$pagesize,$filter,$sort,$special), $model->count($filter,$special));
    }

    private function listResults($model, $lst,$total=null, $noexport=FALSE) {
        if($total === null) {
            $total = sizeof($lst);
        }

        $retval=array();
        $retval["list"]=array();

        if(!empty($lst) && is_array($lst)) {
            array_walk($lst,function($v,$k) use (&$retval,$model,$noexport) {
                $retval["list"][]=$noexport ? $v : $model->export($v);
            });
            $retval["total"] = $total;
        }
        else {
            global $wpdb;
            $str = mysqli_error( $wpdb->dbh );
            error_log('ERROR:' .$str);
            $retval['list']=array();
            $retval['total']=0;
        }
        return $retval;
    }

    private function loadModel($name) {
        require_once(__DIR__ . '/lib/base.php');
        require_once(__DIR__ . "/models/".strtolower($name).".php");
        error_log('instantiation');
        $name="\\WPBroker\\$name";
        return new $name();
    }
}