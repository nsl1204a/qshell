<?php

require_once "PAF/PAFRecordSet.php";
require_once "genresRD.php";

class genresRS extends PAFRecordSet
{
    function genresRS(&$ds)
    {
         $this->PAFRecordSet($ds);
    }

    function exec()
    {
        $query= "SELECT gen_id, gen_name, gen_description FROM gen_genre ORDER BY gen_id;";
        $this->result= $this->dataSource->runQuery ($query);

        if ( PEAR::isError ($this->result) )
            return $this->result;
        else
            return true;
    }

    function count()
    {
        return $this->result->numRows();
    }

    function next()
    {
        $row= $this->result->fetchRow (DB_FETCHMODE_ASSOC);
        if ( is_null ($row) )
        { return false; }
        else
        { return new genresRD($row); }
    }
}
?>