<?php

/**
* @desc Clase que maneja un tree en base de datos, con el momdelo nested set. Basado en el articulo de:
*       http://dev.mysql.com/tech-resources/articles/hierarchical-data.html
*       Esta clase asume que ya esta incluida la libreria PEAR, no la incluye.
* @author    Ionatan Wiznia <ionatan.wiznia@novabase.es>
* @copyright PRISACOM S.A
* @package PAF
* TODO: Ver transacciones, ahora se hace locktable, no se si hay que hacer rollback o como va...
*/
class PAFDBNestedTree {
    var $db;
    var $table;
    var $idField;
    var $lftField;
    var $rgtField;
    var $lastId;
    var $encoding = 'utf8';

    /**
    * @desc Constructor
    * @param string DSN De la base de datos.
    * @param string Nombre de la tabla del tree.
    * @param string Campo que es id en esa tabla.
    * @param string Campo que indica el left de un item.
    * @param string Campo que indica el right de un item.
    * @access public
    */
    function PAFDBNestedTree($dsn, $table, $idField = "id", $lftField = "lft", $rgtField = "rgt") {
        $this->db = $this->_connect($dsn);
        if(PEAR::isError($this->db))
            return $this->db;

        $this->table = $table;
        $this->idField = $idField;
        $this->lftField = $lftField;
        $this->rgtField = $rgtField;
    }

    /**
    * @desc Se conecta a la base de datos dada en el $dsn.
    * @param string DSN de la base de datos
    * @return mixed PEAR:ERROR si error, objeto de coneccion de PEAR si OK.
    */
    function _connect($dsn)
    {
        $db = DB::connect($dsn, false);
        if(DB::isError($db))
           return PEAR::raiseError("Error al conectar con la BD $this->dsn");
        else
            return $db;
    }

    /**
    * @desc Ejecuta una query en la base de datos guardada en $this->db
    * @param string Query SQL.
    * @return resultado de PEAR
    */
    function _execQuery($query){
        $res = $this->db->query("SET NAMES " . $this->encoding . ";");
        $res = $this->db->query($query);
		// Comentado
        //if (PEAR::IsError($res)) { echo "Unable to run query $query\n".$res->getMessage()."\n"; die;}
        return $res;
    }

    function _getLastInsertId()
    {
        return mysql_insert_id($this->db->connection);
    }

    /**
    * @desc Dado un sql devuelve todos los resultados en un array. Si $onlySQL es true, devuelve el SQL intacto.
    * @param string Query SQL.
    * @param boolean Si true devuelve el SQL intacto, o sea, no hace nada.
    */
    function fetchAllQuery($query, $onlySQL = false)
    {
        if($onlySQL)
            return $query;

        $ret = false;
        $res = $this->_execQuery($query);
        if(!PEAR::IsError($res))
        {
            $ret = Array();
            while($aux = $res->fetchRow(DB_FETCHMODE_ASSOC))
                $ret[] = $aux;
			return $ret;
        }else{
			return $res;
		}
    }

    /**
    * @desc Devuelve un tree completo, desde el nodo indicado.
    * @param integer Id del nodo desde el cual se quiere traer el tree completo
    *        (puede ser un subtree, no necesita ser un nodo root)
    * @param boolean True si se quiere que no devuelva los resultados, que devuelva el SQL (esto es util
    *        si se quiere utilzar como una subquery)
    * @param array Array de fields de la tabla que se quieren traer, default es todos.
    * @param boolean Si true, solo devuelve las hojas de ese arbol, en lugar de todos los nodos.
    * @param string String para agregarle al where
    * @return mixed false si error, array si OK.
    */
    function getTree($id, $onlySQL = false, $fields = false, $onlyLeafs = false, $additionalWhere = "")
    {
        if($fields)
        {
            foreach($fields as $i => $field) // Aca le agrega el nombre de la tabla a los campos
                $fields[$i] = "node." . $field;
        }

        $sql = "SELECT " . ($fields ? implode(", ", $fields) : "node.*") . " FROM " . $this->table . " as node, " . $this->table . " as parent ";
        $sql .= "WHERE node." . $this->lftField . " BETWEEN parent." . $this->lftField . " AND parent." . $this->rgtField;
        $sql .= " AND parent." . $this->idField . " = " . $id;
        if($onlyLeafs)
            $sql .= " AND node." . $this->rgtField . " = node." . $this->lftField . " + 1 " . ($additionalWhere ? " AND " . $additionalWhere : "");
        $sql .= " ORDER BY node." . $this->lftField;
        $ret = $this->fetchAllQuery($sql, $onlySQL);

        return $ret;
    }

    /**
    * @desc Devuelve un tree completo, desde el nodo indicado con sus depths incluidos.
    * @param integer Id del nodo desde el cual se quiere traer el tree completo
    *        (puede ser un subtree, no necesita ser un nodo root)
    * @param boolean True si se quiere que no devuelva los resultados, que devuelva el SQL (esto es util
    *        si se quiere utilzar como una subquery)
    * @param array Array de fields de la tabla que se quieren traer, default es todos.
    * @param string String para agregarle al where
    * @return mixed false si error, array si OK.
    */
    function getTreeDepth($id, $onlySQL = false, $fields = false, $additionalWhere = "")
    {
        if($fields)
        {
            foreach($fields as $i => $field) // Aca le agrega el nombre de la tabla a los campos
                $fields[$i] = "node." . $field;
        }

        $sql = "SELECT " . ($fields ? implode(", ", $fields) : "node.*") . ", (COUNT(parent." . $this->idField . ") - (subTree.depth + 1)) AS depth FROM " . $this->table . " as node, " . $this->table . " as parent, " . $this->table . " as subParent, ";
        $sql .= "(SELECT node." . $this->idField . ", (COUNT(parent.name) - 1) AS depth FROM " . $this->table . " as node, " . $this->table . " as parent WHERE node." . $this->lftField . " BETWEEN parent." . $this->lftField . " AND parent." . $this->rgtField . " AND node." . $this->idField . " = $id GROUP BY node." . $this->idField . " ORDER BY node." . $this->lftField . ") as subTree ";
        $sql .= "WHERE node." . $this->lftField . " BETWEEN parent." . $this->lftField . " AND parent." . $this->rgtField;
        $sql .= " AND node." . $this->lftField . " BETWEEN subParent." . $this->lftField . " AND subParent." . $this->rgtField;
        $sql .= " AND subParent." . $this->idField . " = subTree." . $this->idField . " " . ($additionalWhere ? " AND " . $additionalWhere : "");
        $sql .= " GROUP BY node." . $this->idField . " ORDER BY node." . $this->lftField;
        $ret = $this->fetchAllQuery($sql, $onlySQL);

        return $ret;
    }

    /**
    * @desc Devuelve un tree completo, desde el nodo indicado, en un array que lo representa. Es como el
    * 		getTreeDepth, pero te devuelve cada nodo con dentro un array de "children" con sus hijos.
    * @param integer Id del nodo desde el cual se quiere traer el tree completo
    *        (puede ser un subtree, no necesita ser un nodo root)
    * @param boolean True si se quiere que no devuelva los resultados, que devuelva el SQL (esto es util
    *        si se quiere utilzar como una subquery)
    * @param array Array de fields de la tabla que se quieren traer, default es todos.
    * @param string String para agregarle al where
    * @return mixed false si error, array si OK.
    */
    function getTreeDepthArray($id, $onlySQL = false, $fields = false, $additionalWhere = "")
    {
	    $res = $this->getTreeDepth($id, $onlySQL = false, $fields = false, $additionalWhere);
	    if(!$res)
	    	return false;

	    if(count($res) == 0)
	    	return Array();

		$i = 0;
		$ret = $res[0];
		$this->makeDepthArray($res, $i, $ret);
		return $ret;
	}

	/**
	* @desc Dado un array de resultados con depths (como el que devuelve getTreeDepth, el numero de nodo desde el
	* 		cual empezar y el nodo del que se empieza, devuelve un array anidado en cuya propiedad children estan los
	* 		hijos de cada nodo.
	* @param array Resultados como los entregados por getTreeDepth, basicamente es un array que tiene que tener
	* 				seteado el campo depth.
	* @param integer Numero de indice del array por el cual va procesando.
	* @param array Nodo del tree, por el cual va procesando, basicamente es el nodo $i del $res.
	* @return array Array anidado con los nodos y en el indice "children" sus hijos. No lo devuelve sino que lo deja
	* 				en $node y en $i la posicion por la cual va.
	*/
	function makeDepthArray($res, &$i, &$node)
	{
		for($i++; $i < count($res) && $res[$i]["depth"] > $node["depth"]; $i++)
		{
			$node["children"][] = $res[$i];
			if($res[$i + 1]["depth"] == $res[$i]["depth"] + 1)
			{
				$b = true;
				$this->makeDepthArray($res, $i, $node["children"][count($node["children"]) -1]);
			}
		}
		if($res[$i]["depth"] <= $node["depth"])
			$i--;
	}

    /**
    * @desc Dado un nodo, devuelve su path completo (o sea, todos sus padres) ordenados de padre a hijo.
    * @param integer Id del nodo.
    * @param boolean True si se quiere que no devuelva los resultados, que devuelva el SQL (esto es util
    *        si se quiere utilzar como una subquery)
    * @param array Array de fields que se quieren traer, por default todos.
    * @param string String para agregarle al where
    * @return mixed false si error, array si OK.
    */
    function getNodePath($id, $onlySQL = false, $fields = false, $additionalWhere = "")
    {
        if($fields)
        {
            foreach($fields as $i => $field) // Aca le agrega el nombre de la tabla a los campos
                $fields[$i] = "parent." . $field;
        }

        $sql = "SELECT " . ($fields ? implode(", ", $fields) : "parent.*") . " FROM " . $this->table . " as node, " . $this->table . " as parent ";
        $sql .= "WHERE node." . $this->lftField . " BETWEEN parent." . $this->lftField . " AND parent." . $this->rgtField;
        $sql .= " AND node." . $this->idField . " = " . $id . " " . ($additionalWhere ? " AND " . $additionalWhere : "");
        $sql .= " ORDER BY parent." . $this->lftField;
        $ret = $this->fetchAllQuery($sql, $onlySQL);

        return $ret;
    }

    /**
    * @desc Dado un nodo, devuelve sus hijos (que no es lo mismo que descendientes), solo hijos directos.
    * @param integer Id del nodo.
    * @param boolean True si se quiere que no devuelva los resultados, que devuelva el SQL (esto es util
    *        si se quiere utilzar como una subquery)
    * @param array Array de fields que se quieren traer, por default todos.
    * @param boolean Si se quiere que se incluya el nodo dado (el de $id) o no. Defaul false.
    * @param string String para agregarle al where
    * @return mixed false si error, array si OK.
    */
    function getChildNodes($id, $onlySQL = false, $fields = false, $includeThis = false, $additionalWhere = "")
    {
        if($fields)
        {
            foreach($fields as $i => $field) // Aca le agrega el nombre de la tabla a los campos
                $fields[$i] = "node." . $field;
        }

        $sql = "SELECT " . ($fields ? implode(", ", $fields) : "node.*") . ", (COUNT(parent." . $this->idField . ") - subTree.depth + 1) as depth FROM " . $this->table . " as node, " . $this->table . " as parent, " . $this->table . " as subParent, ";
        $sql .= "(SELECT node." . $this->idField . ", (COUNT(parent.name) - 1) AS depth FROM " . $this->table . " as node, " . $this->table . " as parent WHERE node." . $this->lftField . " BETWEEN parent." . $this->lftField . " AND parent." . $this->rgtField . " AND node." . $this->idField . " = $id GROUP BY node." . $this->idField . " ORDER BY node." . $this->lftField . ") as subTree ";
        $sql .= "WHERE node." . $this->lftField . " BETWEEN parent." . $this->lftField . " AND parent." . $this->rgtField;
        $sql .= " AND node." . $this->lftField . " BETWEEN subParent." . $this->lftField . " AND subParent." . $this->rgtField;
        $sql .= " AND subParent." . $this->idField . " = subTree." . $this->idField . " " . ($additionalWhere ? " AND " . $additionalWhere : "") . " GROUP BY node." . $this->idField;
        $sql .= " HAVING depth " . ($includeThis ? "<=" : "=") . " 1 ORDER BY parent." . $this->lftField;
        $ret = $this->fetchAllQuery($sql, $onlySQL);

        return $ret;
    }

    /**
    * @desc Trae datos del tree relacionandolo con otra tabla, por ejemplo sirve para que (en una sola consulta)
    *       Si mi tree son categorias y tengo una tabla de productos, me traiga la cantidad de productos de cada
    *       una de los nodos de un tree.
    * @param integer Id del tree o del nodo del tree del que se quiere traer la info.
    * @param boolean True si se quiere que no devuelva los resultados, que devuelva el SQL (esto es util
    *        si se quiere utilzar como una subquery)
    * @param string Tabla con la cual se relaciona
    * @param string Es el field que conecta $table con la tabla de tree.
    * @param string Funcion de SQL y campo al que aplicarlo, por ej: COUNT(productos.id)
    * @param array Array de fields que se quieren traer. OJO! Tener en cuenta que hay 2 tablas en la
    *        consulta. La del tree y $table, asique conviene mandarlos con tabla.field para evitar problemas
    * @param string String para agregarle al where
    * @return mixed false si error o array si OK.
    */
    function getAggregatedDataFromTree($id, $onlySQL = false, $table, $connectField, $function, $fields = false, $additionalWhere = "")
    {
        $sql = "SELECT " . ($fields ? implode(", ", $fields) : "*") . ", $function ";
        $sql .= "FROM " . $this->table . " as node, " . $this->table . " as parent, " . $this->table . " as tree ";
        $sql .= "WHERE node." . $this->lftField ." BETWEEN parent." . $this->lftField . " AND " . $this->rgtField;
        $sql .= " AND node." . $this->idField . " = " . $table . "." . $connectField;
        $sql .= " AND node." . $this->lftField . " BETWEEN tree." . $this->lftField . " AND tree." . $this->rgtField;
        $sql .= " AND tree." . $this->idField . " = " . $id . ($additionalWhere ? " AND " . $additionalWhere : "");
        $sql .= " GROUP BY parent." . $this->idField .  " ORDER BY node." . $this->lftField;
        $ret = $this->fetchAllQuery($sql, $onlySQL);

        return $ret;
    }

    /**
    * @desc Agrega un nodo al tree.
    * @param array Array asociativo con "campo" => "valor" con los valores del nodo a insertar (no hace
    *        falta mandar ni left ni right, ya que eso es lo que calcula automaticamente.
    * @param integer Id del padre. Puede ser null para que sea root de un arbol.
    * @param integer Id del nodo del cual lo tiene que insertar despues (tiene que ser un hijo de $parentId)
    * @return boolean False en caso de error, true en caso de OK. Ademas setea en la propiedad lastId de la clase
    *                 el id de la categoria que acaba de insertar.
    */
    function insertNode($values, $parentId = null, $afterId = null){
        if($parentId === null) // Add a child of nobody: a root
            $raise = 0;
        else
        { // Add a child of $parentId
            $res = $this->_execQuery("SELECT " . $this->lftField . ", " . $this->rgtField . " FROM " . $this->table . " WHERE " . $this->idField . " = ". $parentId);
            $row = $res->fetchRow(DB_FETCHMODE_ASSOC);
            if (!$row) { echo "Unable to find parentId $parentId\n"; die;}
            $lft = $row[$this->lftField];
            $rgt = $row[$this->rgtField];
            if ($afterId === null)
                $raise = $lft;
            else
            {
                $res = $this->_execQuery("SELECT " . $this->rgtField . " FROM " . $this->table . " WHERE " . $this->idField . " = ". $afterId);
                $row = $res->fetchRow(DB_FETCHMODE_ASSOC);
                if (!$row) { echo "Unable to find afterId $afterId\n"; die;}
                $afterRgt = $row[$this->rgtField];
                if ( ($afterRgt < $rgt) || ($afterRgt > $rgt) ) { echo "afterId $afterId is not a child of parentId $parentId\n"; die; }
                    $raise = $afterRgt;
            }
        }

        $lft = $raise + 1;
        $rgt = $raise + 2;
        $data = array_merge(array_values($values), Array($lft, $rgt));
        array_walk($data, create_function('&$item, $key', '$item="\'" . $item . "\'";'));
        $data = implode(", ", $data);

        //$res = $this->_execQuery("LOCK TABLE " . $this->table . " WRITE");
        $res = $res & $this->_execQuery("UPDATE " . $this->table . " SET " . $this->rgtField . " = " . $this->rgtField . " + 2 WHERE " . $this->rgtField . " > " . $raise);
        $res = $res & $this->_execQuery("UPDATE " . $this->table . " SET " . $this->lftField . " = " . $this->lftField . " + 2 WHERE " . $this->lftField . " > " . $raise);
        $res = $res & $this->_execQuery("INSERT INTO " . $this->table . "(" . implode(", ", array_keys($values)) . ", " . $this->lftField . ", " . $this->rgtField . ") VALUES (" . $data . ")");
        $this->lastId = $this->_getLastInsertId();
        //$res = $res & $this->_execQuery("UNLOCK TABLES");

        return $res;
    }

    /**
    * @desc Borra un nodo del tree. Puede borrar solo ese nodo o todos sus hijos tambien.
    * @param integer Id del nodo a borrar
    * @param boolean $recursive Si true, borra el nodo y todos sus hijos, sino solo a el y promueve a sus hijos a un nivel mas arriba.
    * @param boolean $returnAffectedNodes Si true, devuelve un array con los ids de los nodos que afecto la consulta.
    * @return mixed False en caso de error, un array con los ids de todos los nodos afectados, si se mando $returnAffectedNodes sino solo con el id del nodo mandado
    */
    function deleteNode($id, $recursive = true, $returnAffectedNodes = false){
        $nodes = Array();
        if($returnAffectedNodes && $recursive)
        {
            $nodes = $this->getTree($id, false, Array($this->idField), false);
            foreach($nodes as $i => $node)
                $nodes[$i] = $nodes[$i][$this->idField];
        }
        else
            $nodes[] = $row[$this->idField];

        //$res = $this->_execQuery("LOCK TABLE " . $this->table . " WRITE");
        $res = $this->_execQuery("SELECT " . $this->rgtField . ", " . $this->lftField . ", " . $this->rgtField . " - " . $this->lftField . " + 1 as width FROM " . $this->table . " WHERE " . $this->idField . " = ". $id);
        $row = $res->fetchRow(DB_FETCHMODE_ASSOC);
        if (!$row) { echo "Unable to find Id $id\n"; die;}
        $lft = $row[$this->lftField];
        $rgt = $row[$this->rgtField];
        $width = $row["width"];

        if($recursive)
            $res = $this->_execQuery("DELETE FROM " . $this->table . " WHERE " . $this->lftField . " BETWEEN $lft AND $rgt");
        else
        {
            $res = $this->_execQuery("DELETE FROM " . $this->table . " WHERE " . $this->lftField . " = $lft");
            $res = $res & $this->_execQuery("UPDATE " . $this->table . " SET " . $this->rgtField . " = " . $this->rgtField . " - 1, " . $this->lftField . " = " . $this->lftField . " - 1  WHERE " . $this->lftField . " BETWEEN $lft AND $rgt");
            $width = 2;
        }
        $res = $res & $this->_execQuery("UPDATE " . $this->table . " SET " . $this->rgtField . " = " . $this->rgtField . " - $width WHERE " . $this->rgtField . " > $rgt");
        $res = $res & $this->_execQuery("UPDATE " . $this->table . " SET " . $this->lftField . " = " . $this->lftField . " - $width WHERE " . $this->lftField . " > $rgt");
        //$res = $res & $this->_execQuery("UNLOCK TABLES");

        if($res)
            return $nodes;
        else
            return false;
    }

    /**
    * @desc Mueve un nodo de lugar. Puede moverlo a un padre (al final o al principio) o a un hermano
    * 		(despues o antes). Puede moverlo recursivamente o solo (y asciende a sus hijos un nivel)
    * @param int Id del nodo a mover
    * @param int Id del nodo en el cual se quiere insertar (puede ser el que sera el padre o el hermano)
    * @param boolean True si se quiere insertar antes, false si se quiere insertar despues. En el caso de que el
    * 		 idInsert sea el padre, insertarlo antes significa primero de los hijos de ese padre y despues, ultimo
    * @param boolean True si el idInsert es el id de lo que queremos que sea el padre, false es hermano.
    * @param boolean True si se quieren mover todos los subnodos de este nodo. TODO: Solo es recursivo, por ahora.
    * @return boolean True si OK, false si error.
    */
    function moveNode($id, $idInsert, $before = false, $isParent = false, $recursive = true) {
        //$res = $this->_execQuery("LOCK TABLE " . $this->table . " WRITE");

        // Trae el left width y right del nodo y del nodo donde lo vamos a insertar
        $res = $this->_execQuery("SELECT " . $this->rgtField . ", " . $this->lftField . ", " . $this->rgtField . " - " . $this->lftField . " + 1 as width FROM " . $this->table . " WHERE " . $this->idField . " = ". $id);
        $row = $res->fetchRow(DB_FETCHMODE_ASSOC);
        if (!$row) { echo "Unable to find Id $id\n"; die;}
        $lft = $row[$this->lftField];
        $rgt = $row[$this->rgtField];
        $width = $row["width"];

        // setea el nodo y subnodos a -valor para que no se confunda despues
        $res = $res & $this->_execQuery("UPDATE " . $this->table . " SET " . $this->rgtField . " = -" . $this->rgtField . ", " . $this->lftField . " = -" . $this->lftField . " WHERE " . $this->lftField . " BETWEEN $lft AND $rgt");

        // Mueve todos los nodos que estan a la derecha del que muevo hacia la izquierda, cantidad: width
        $res = $res & $this->_execQuery("UPDATE " . $this->table . " SET " . $this->lftField . " = " . $this->lftField . " - $width WHERE " . $this->lftField . " > $rgt");
        $res = $res & $this->_execQuery("UPDATE " . $this->table . " SET " . $this->rgtField . " = " . $this->rgtField . " - $width WHERE " . $this->rgtField . " > $rgt");

        // Traigo el idInsert (lo traigo aca para traer el valor actualizado)
        $res = $this->_execQuery("SELECT " . $this->rgtField . ", " . $this->lftField . ", " . $this->rgtField . " - " . $this->lftField . " + 1 as width FROM " . $this->table . " WHERE " . $this->idField . " = ". $idInsert);
        $row = $res->fetchRow(DB_FETCHMODE_ASSOC);
        if (!$row) { echo "Unable to find Id $idInsert\n"; die;}
        $lftInsert = $row[$this->lftField];
        $rgtInsert = $row[$this->rgtField];
        $widthInsert = $row["width"];
        $index = (!$before ? ($rgtInsert - ($isParent ? 1 : 0)) : ($lftInsert - ($isParent ? 0 : 1)));

        // Hace lugar en el lugar nuevo
        $res = $res & $this->_execQuery("UPDATE " . $this->table . " SET " . $this->lftField . " = " . $this->lftField . " + $width WHERE " . $this->lftField . " > $index");
        $res = $res & $this->_execQuery("UPDATE " . $this->table . " SET " . $this->rgtField . " = " . $this->rgtField . " + $width WHERE " . $this->rgtField . " > $index");

        // Mete los nodos en donde corresponde
        $res = $res & $this->_execQuery("UPDATE " . $this->table . " SET " . $this->lftField . " = 0 - " . $this->lftField . " - ($lft - $index) + 1, " . $this->rgtField . " = 0 - " . $this->rgtField . " - ($lft - $index) + 1 WHERE " . $this->lftField . " < 0"); //BETWEEN -$lft AND -$rgt"
        //$res = $res & $this->_execQuery("UNLOCK TABLES");

        return $res;
	}
}
?>
