<?php
namespace Snake\Package\Base;

abstract class QueryFactory {

    abstract function newSelection(IdentityObject $obj);

    function buildWhere(IdentityObject $obj) {
        if ($obj->isVoid()) {
            return array("", array());
        } 
        $compstrings = array();
        $values = array();
        foreach ($obj->getComps() as $comp) {
			//in()特殊处理
			if ("in" == $comp['operator']) {
				if (!is_array($comp['value']) || empty($comp['value'])) { 
					throw new \Exception('in values is not array');
				}
				$str = implode(",", $comp['value']);
				$compstrings[] = "{$comp['name']} {$comp['operator']}({$str})";
			}
			else if ("between" == $comp['operator']) {
				if (!is_array($comp['value']) || empty($comp['value'])) { 
					throw new \Exception('in values is not array');
				}
				$str = implode(" and ", $comp['value']);
				$compstrings[] = "{$comp['name']} {$comp['operator']} {$str}";
			}
			else {
				$compstrings[] = "{$comp['name']} {$comp['operator']}:{$comp['name']}";
				$values[$obj->getPrefix() . $comp['name']] = $comp['value'];
			}
        }

		$extra = '';
		$extraOp = $obj->getExtra();
		if (!empty($extraOp)) {
			foreach ($extraOp as $op) {
				$extra .= " " . $op['operation'] . " " . $op['name'];	
			}
		}

        $where = "WHERE " . implode(" AND ", $compstrings) . $extra;
        return array($where, $values);
    }
	abstract function newUpdate(DomainObject $obj); 

    protected function buildStatement($table, array $fields, array $conditions=null) { 
        $terms = array();
        if (!is_null($conditions)) { 
            $query  = "UPDATE {$table} SET ";
            //$query .= implode ( " =:,", array_keys( $fields ) )." =: "; 
			$i = 0;
			if ($fields['add_himself'] == 1) {
				$flag = 1;
				unset($fields['add_himself']);
			}
			foreach ($fields AS $key => $value) {
				if ($i != 0) {
					$query .= ","; 
				}
				if ($flag == 1 && (strpos($value, '+') || /*strpos($value, '*')*/ strpos($value, '-'))) {//|| strpos($value, '/')) { //comment out first
					$query .= $key . "=" . $value;
					unset($fields[$key]);
				}
				else {
					$query .= $key . "=:" . $key;
				}
				$i++;
			}
            $terms = array_values($fields);
            $cond = array();
            $query .= " WHERE ";
            foreach ($conditions as $key=>$val) { 
				$cond[] = "$key =:$key";
                $terms[] = $val;
				$fields[$key] = $val;
            }   
            $query .= implode(" AND ", $cond);
        } 
		elseif (empty($fields['m_insert'])) {
			$keys = array_keys($fields);
            $query  = "INSERT IGNORE INTO {$table} (";
            $query .= implode(",", $keys);
            $query .= ") VALUES (";
            foreach ($keys as $key) { 
				$query .= ":" . $key . ",";
            }   
            //$query .= implode(",", $qs);
			$query = rtrim($query, ",");
            $query .= ")";
        }   
		elseif (isset($fields['m_insert']) && $fields['m_insert'] == 1) {
			$keys = $fields['keys'];
			$values = $fields['values'];
			$query = "INSERT IGNORE INTO {$table} (" . $keys . ") VALUES ";
			$i = 0;
			foreach ($values AS $value) {
				if ($i != 0) {
					$query .= ",";
				}
				$query .= " (" . $value . ")";
				$i++;
			}
			$fields = array();
		}
        return array($query, $fields);
	}

	function buildDelete($table, $cond) {
		$sql = "DELETE FROM " . $table . " WHERE ";
		$i = 0;
		foreach ($cond AS $key => $value) {
			if ($i != 0) {
				$sql .= " AND ";
			}
			$sql .= $key . "=:" . $key;
			$i++;
		}
		$result[] = $sql;
		$result[] = $cond;
		return $result;
	}
}

?>
