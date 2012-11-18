<?php
namespace Snake\Libs\DB;
/**
 *	数据库封装操作
 *  @author guoshuaitan@meilishuo.com
 *  @since 2012-06-28
 *  @version 1.0
 */
class MakeSql{

	private $mSql;

	public function  __construct(){
		$this->mSql = "";
 	}

	function MakeSqlType( $type, $list ){
		if (!isset($list['table'])) return false;
		switch( $type ){
			case 'insert':
				return $this->makeInsert($list);
			case 'select':
			        return $this->makeSelect($list);
			case 'update':
			        return $this->makeUpdate($list);
			case 'delete':
					return $this->makeDelete($list);
 		 }
	}
 
	/**
	 *  形成插入语句
	 *  @params: list['insert'] = array('key' => 'value') key为插入的键值，value插入的值
	 *  @return: string sql
	 */
	function makeInsert( $list){
		if (!isset($list['insert'])) return false;
		$colum_key = array_keys( $list['insert'] );
		$colum_value = array_values($list['insert']);
		$this->mSql = "INSERT INTO " . $list["table"] . "( " . join( ',', $colum_key ). " ) VALUES( " . join( "," , $colum_value ) . ")";
		return $this->mSql;
 	}
 
	/**
	 *  查找语句
	 *  @params: list['s_colum'] 查找的列
	 *  @params: list['where'] where 条件
	 *  @params: list['where_in'] in 条件
	 */
	function makeSelect($list) {
		$colum_value = $list['colum'];
		$this->mSql = "select $colum_value from " . $list['table'] ;
		$this->mSql = $this->assignWhere($list, $this->mSql);

		if(isset($list['esp'])) {
			$this->mSql .=  $list['esp'];	
		}
		if(isset($list['order'])) {
			$this->mSql .= " order by " . $list['order'] ;	
		}
		if(isset($list['limit'])) {
			$this->mSql .= " limit " . $list['limit'] ;	
		}
		return $this->mSql;
	} 

	/**
	 * 更新语句
	 * 
	 */
	function makeUpdate($list) {
		$this->mSql = "UPDATE " . $list['table'] . " SET ";
		foreach($list['update']  as $key => $value) {
			$this->mSql .= "$key = $value ,";
		} 
		$this->mSql = rtrim( $this->mSql, ',');
		$this->mSql = $this->assignWhere($list, $this->mSql);
 		return $this->mSql;
	}
    /**
	 * 删除语句
	 *
	 */
	function makeDelete($list) {
		$this->mSql = "DELETE FROM ".$list['table'];
		$this->mSql = $this->assignWhere($list, $this->mSql);
		return $this->mSql;
 	}

	/**
     * 处理where语句
	 *
	 */
	function assignWhere($list, $sql) {
		if(!isset($list['where']) && !isset($list['where_in']) && !isset($list['where_not']) && !isset($list['where_in_not'])) {
			return $sql;
		}
		$sql .= " where 1 = 1 "; 
		if (isset($list['where'])) {
			foreach($list['where'] as $key => $value) {
				$sql .= " and $key = $value ";	
			}
		}
		if (isset($list['where_in'])) {
			foreach($list['where_in'] as $key => $value) {
				$sql .= " and $key in ($value) ";	
			}	
		}
		if (isset($list['where_in_not'])) {
			foreach($list['where_in_not'] as $key => $value) {
				$sql .= " and $key not in ($value) ";	
			}	
		}
		if (isset($list['where_not'])) {
			foreach($list['where_not'] as $key => $value) {
				$sql .= " and $key != $value ";	
			}	
		}
		return $sql;
	}
} 
