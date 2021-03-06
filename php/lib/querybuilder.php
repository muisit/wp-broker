<?php

/**
 * Wp-Broker QueryBuilder Model
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

 class QueryBuilder {
    public function __construct($model, $issub=false) {
        $this->_model=$model;
        $this->_issub=$issub;
    }

    public function sub() {
        $qb=new QueryBuilder($this, true);
        return $qb;
    }

    private $_action="select";
    public function delete() {
        if($this->_issub) return "";
        $sql = "DELETE FROM ".$this->_from;

        if(sizeof($this->_where_clauses)) {
            $first=true;
            foreach($this->_where_clauses as $c) {
                if($first) {
                    $first=false;
                    $sql.=" WHERE ".$c[1];
                }
                else {
                    $sql .= " ".$c[0] . ' '.$c[1];
                }
            }
        }

        return $this->_model->prepare($sql,$this->_where_values);
    }

    public function insert() {
        if($this->_issub) return "";
        $sql = "INSERT INTO ".$this->_from;
        
        // no joins

        $values=array();
        $fields=array();
        foreach($this->_select_fields as $f=>$n) {            
            $id=uniqid();
            $fields[]=$f;
            $values[]="{".$id."}";
            $this->_where_values[$id]=$n;
        }
        $sql.="(".implode(',',$fields) .") VALUES (".implode(',',$values).")";

        // no complicated additional clauses
        return $this->_model->prepare($sql,$this->_where_values);    
    }

    public function update() {
        if($this->_issub) return "";
        $sql = "UPDATE ".$this->_from;
        
        if(sizeof($this->_joinclause)) {
            foreach($this->_joinclause as $jc) {
                $sql.= " ".$jc["dir"]." JOIN ".$jc["tab"]." ".$jc['al']." ON ".$jc['cl'];
            }
        }

        $sql.=" SET ";
        $fields=array();
        foreach($this->_select_fields as $f=>$n) {
            $id=uniqid();
            $fields[]=$f."={$id}";
            $this->_where_values[$id]=$n;
        }
        $sql.=implode(',',$fields);

        if(sizeof($this->_where_clauses)) {
            $first=true;
            foreach($this->_where_clauses as $c) {
                if($first) {
                    $first=false;
                    $sql.=" WHERE ".$c[1];
                }
                else {
                    $sql .= " ".$c[0] . ' '.$c[1];
                }
            }
        }
        return $this->_model->prepare($sql,$this->_where_values);
    }

    public function count() {
        $result = $this->_doget();
        if(empty($result) || !is_array($result)) return 0;
        return intval($result[0]->cnt);
    }

    public function first() {
        // calling first on a sub-selection is not supported
        if($this->_issub) return $this->_dosub();
        return $this->_doget(true);
    }
    public function get() {
        if($this->_issub) return $this->_dosub();
        return $this->_doget();
    }
    private function _doget($dofirst=false) {
        $sql = strtoupper($this->_action)." "
            .implode(',', array_keys($this->_select_fields))
            ." FROM ".$this->_from;

        if(sizeof($this->_joinclause)) {
            foreach($this->_joinclause as $jc) {
                $sql.= " ".$jc["dir"]." JOIN ".$jc["tab"]." ".$jc['al']." ON ".$jc['cl'];
            }
        }
        if(sizeof($this->_where_clauses)) {
            $first=true;
            foreach($this->_where_clauses as $c) {
                if($first) {
                    $first=false;
                    $sql.=" WHERE ".$c[1];
                }
                else {
                    $sql .= ' '.$c[0] . ' '.$c[1];
                }
            }
        }

        if(sizeof($this->_groupbyclause)) {
            $sql.=" GROUP BY ".implode(',',$this->_groupbyclause);
        }
        if(sizeof($this->_havingclause)) {
            $sql.=" HAVING ".implode(',',$this->_havingclause);
        }
        if(sizeof($this->_orderbyclause)) {
            $sql.=" ORDER BY ".implode(',',$this->_orderbyclause);
        }
        if($dofirst) {
            $sql .= " LIMIT 1";
        }
        else {
            if(!empty($this->_limit) && intval($this->_limit) > 0) {
                $sql .= " LIMIT ".intval($this->_limit);
            }
            if(!empty($this->_offset) && !empty($this->_limit)) {
                $sql .= " OFFSET ".intval($this->_offset);
            }
        }

        return $this->_model->prepare($sql,$this->_where_values,$dofirst);
    }

    private function _dosub() {
        $sql="";
        if(sizeof($this->_where_clauses)) {
            $first=true;
            foreach($this->_where_clauses as $c) {
                if($first) {
                    $first=false;
                    $sql.=$c[1];
                }
                else {
                    $sql .= ' '.$c[0] . ' '.$c[1];
                }
            }
        }
        $this->_model->_where_clauses[]=array('AND','('.$sql.')');
        $this->_model->_where_values=array_merge($this->_model->_where_values, $this->_where_values);
        return $this;
    }

    private $_select_fields=array();
    public function reselect($f=null) {
        $this->_select_fields=array();
        return $this->select($f);
    }
    public function select($f=null) {
        $this->_action="select";
        if(empty($f)) {
            return $this;
        }
        return $this->fields($f);
    }

    public function fields($f) {
        if(empty($f)) {
            return $this;
        }
        if(is_string($f)) {
            $this->_select_fields[$f]=true;
        }
        else if(is_array($f)) {
            foreach($f as $k=>$v) {
                if(is_numeric($k)) {
                    $this->_select_fields[$v]=true;
                }
                else {
                    $this->_select_fields[$k] = true;
                }
            }
        }
        return $this;
    }

    public function set($f,$v=null) {
        if(empty($f)) {
            return $this;
        }
        if(is_string($f)) {
            $this->_select_fields[$f]=$v;
        }
        else if(is_array($f)) {
            foreach($f as $n=>$v) {
                $this->_select_fields[$n]=$v;
            }
        }
        return $this;
    }

    private $_where_clauses=array();
    private $_where_values=array();
    public function where($field,$comparison=null,$clause=null) {
        if(empty($clause)) {
            if(empty($comparison)) {
                if(is_array($field)) {
                    foreach($field as $k=>$v) {
                        $this->_where($k,'=',$v,"AND");
                    }
                }
                else if(is_callable($field)) {
                    $qb=$this->sub();
                    ($field)($qb);
                    $qb->get();
                }
                else {
                    // case where we provide a complete clause
                    $this->_where_clauses[]=array("AND",$field);                    
                }
            }
            else {
                $this->_where($field,'=',$comparison,"AND");
            }
        }
        else {
            $this->_where($field,$comparison,$clause,"AND");
        }
        return $this;
    }

    public function or_where($field,$comparison=null,$clause=null) {
        if(empty($clause)) {
            if(empty($comparison)) {
                if(is_array($field)) {
                    foreach($field as $k=>$v) {
                        $this->_where($k,'=',$v,"OR");
                    }
                }
                else {
                    $this->_where_clauses[]=array("OR",$field." IS NOT NULL");
                }
            }
            else {
                $this->_where($field,'=',$comparison,"OR");
            }
        }
        else {
            $this->_where($field,$comparison,$clause,"OR");
        }
        return $this;
    }

    private function _where($field, $comparison, $clause, $andor="AND") {
        if(strtolower($comparison) == "in") {
            if(is_array($clause)) {
                $clause="('".implode("','",$clause)."')";
            }
            $this->_where_clauses[]=array($andor,"$field IN $clause");
        }
        else {
            $id=uniqid();
            $this->_where_values[$id]=$clause;
            $this->_where_clauses[]=array($andor,$field.' '.$comparison.' {'.$id.'}');
        }
    }

    private $_from=null;
    public function from($table) {
        global $wpdb;
        $this->_from=$wpdb->base_prefix.$table;
        return $this;
    }

    private $_joinclause=array();
    public function join($table, $alias, $onclause, $dr=null) {
        if(empty($dr)) {
            $dr="left";
        }
        global $wpdb;
        $this->_joinclause[]=array("tab"=> $wpdb->base_prefix . $table, "al"=>$alias, "cl"=>$onclause, "dir"=>$dr);
        return $this;
    }

    private $_orderbyclause=array();
    public function orderBy($field, $dr=null) {
        if(is_array($field)) {
            foreach($field as $k=>$v) {
                if(is_numeric($k)) {
                    $this->_orderbyclause[]=$v;
                }
                else if(in_array(strtolower($v),array("asc","desc"))) {
                    $this->_orderbyclause[]="$k $v";
                }
                else {
                    $this->_orderbyclause[]=$k;
                }
            }
        }
        else {
            $this->_orderbyclause[]=trim($field." ".$dr);
        }
        return $this;
    }

    private $_groupbyclause=array();
    public function groupBy($field) {
         if(is_array($field)) {
            foreach($field as $v) {
                $this->_groupbyclause[]=$v;
            }
         }
         else {
            $this->_groupbyclause[]=$field;
        }
        return $this;
    }

    private $_havingclause=array();
    public function having($field) {
         if(is_array($field)) {
            foreach($field as $v) {
                $this->_havingclause[]=$v;
            }
         }
         else {
            $this->_havingclause[]=$field;
        }
        return $this;
    }

    private $_limit=null;
    private $_offset=null;
    public function page($v,$ps=20) {
        if($ps > 0) {
            $this->_limit=$ps;
            if($v<1) $v=1;
            $this->_offset = $v * $ps;
        }
        else {
            $this->_limit=0;
            $this->_offset=0;
        }
        return $this;
    }
    public function limit($v) {
        if(empty($v)) {
            $this->_limit=0;
        }
        else {
            $this->_limit=$v;
        }
        return $this;
    }
    public function offset($v) {
        $this->_offset=$v;
        return $this;
    }
}

