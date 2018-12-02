<?php

require_once "PAF/PAFRecordData.php";

define ("GENREID", "gen_id");
define ("GENRENAME", "gen_name");
define ("GENREDESC", "gen_description");

class genresRD extends PAFRecordData
{
    function genreRD($value)
    {
        $this->PAFRecordData($value);
    }

    function getGenreID()
    { return $this->data[GENREID]; }

    function getGenreName()
    { return $this->data[GENRENAME]; }

    function getGenreDesc()
    { return $this->data[GENREDESC]; }
}
?>
