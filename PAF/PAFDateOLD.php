<?php

  // *****************************************************************************
  // Lenguaje: PHP
  // Copyright 2002 Prisacom S.A.
  // *****************************************************************************

require_once "PAF/PAFObject.php";

/**
  * Clase para el manejo de fechas.
  *
  * @author Sergio Cruz <scruz@prisacom.com>
  * @version $Revision: 1.8 $
  * @package PAF
  */
class PAFDate extends PAFObject
{
    /**
      * Día del mes
      * @var int
      * @access private
      */
    var $day= 0;

    /**
      * Mes del año.
      * @var int
      * @access private
      */
    var $month= 0;

    /**
      * Año
      * @var int
      * @access private
      */
    var $year= -1;

    /**
      * Constructor de la clase.
      * Si no se pasa ningún parámetro el objeto se inicializa con la fecha actual.
      * Si no es especifica algún o algunos de los parámetros asociados se rellenan con los correspondientes
      * a la fecha actual.
      *
      * @param int $day Dia del mes.
      * @param int $month Número del mes.
      * @param int $year Año con cuatro dígitos.
      *
      * @access public
      */
    function PAFDate ($day=0, $month=0, $year=-1)
    {
        // Nos aseguramos que los valores pasados por parámetro son números enteros.
        $day = intval ($day);
        $month = intval ($month);
        $year = intval ($year);

        // Cojemos la fecha de hoy.
        $hoy = getdate ();

        // Comprobamos parametros.
        if ((-1) == $year)
            $this->year = $hoy["year"];
        else
         $this->year = $year;

        if (0 == $month)
            $this->month = $hoy["mon"];
        else
            $this->month = $month;

        if (0 == $day)
            $this->day = $hoy["mday"];
        else
            $this->day = $day;

        // Comprobamos que la fecha sea valida (si no lo es la inicializamos a la_hoy).
        if ( !checkdate ($this->month, $this->day, $this->year) )
        {
            $this->year  = $hoy["year"];
            $this->month = $hoy["mon"];
            $this->day   = $hoy["mday"];
        }

        // Nos aseguramos que todos los valores sean numericos.
        $this->day   = intval ($this->day);
        $this->month = intval ($this->month);
        $this->year  = intval ($this->year);
    }

    /**
      * Fija la fecha con los valores pasados por parámetro.
      *
      * @param int $day Día del mes.
      * @param int $month Número de mes.
      * @param int $year Año con cuatro dígitos.
      *
      * @access public
      */
    function setDate ($day, $month, $year)
    {
        $this->year = intval ($year);

        $month = intval ($month);
        if ( ($month > 0) && ($month < 13) )
            $this->month = $month;

        $day = intval ($day);
        if ( $day > 0 && ($day <= $this->lastDayOfMonth ()) )
            $this->day = $day;
    }

    /**
      * Añade el número de días pasados por parámetro a la fecha actual.
      *
      * @param int $days Número de días que queremos sumar a la fecha actual.
      *
      * @access public
      */
    function addDays ($days)
    {
        $day = $this->day + intval ($days);
        $month = $this->month;
        $year = $this->year;

        while ( !checkdate ($month, $day, $year) )
        {
            $day -= date ("t", mktime(0, 0, 0, $month, 1, $year));
            $month ++;
            if ($month > 12)
            {
                $month = 1;
                $year ++;
            }
        }

        $this->day   = intval ($day);
        $this->month = intval ($month);
        $this->year  = intval ($year);
    }

    /**
      * Resta el número de días pasado por parámetro de la fecha actual.
      *
      * @param int $days Número de días que queremos restar de la fecha actual.
      *
      * @access public
      */
    function substractDays ($days)
    {
        $day = $this->day - intval ($days);
        $month = $this->month;
        $year = $this->year;

        while ( !checkdate ($month, $day, $year) )
        {
            $month --;
            $day += date ("t", mktime(0, 0, 0, $month, 1, $year));
            if ($month < 1)
            {
                $year --;
                $month = 12;
            }
        }
        $this->day   = intval ($day);
        $this->month = intval ($month);
        $this->year  = intval ($year);
    }

    /**
      * Añade el número de semanas pasadas por parámetro a la fecha actual.
      *
      * @param int $weeks Número de semanas que queremos añadir a la fecha actual.
      *
      * @access public
      */
    function addWeeks ($weeks)
    {
        $this->addDays (intval ($weeks) * 7);
    }

    /**
      * Resta el número de semanas pasadas por parámetro de la fecha actual.
      *
      * @param int $weeks Número de semanas que queremos restar de la fecha actual.
      *
      * @access public
      */
    function substractWeeks ($weeks)
    {
        $this->substractDays (intval ($weeks) * 7);
    }

    /**
      * Añade el número de meses pasado por parámetro a la fecha actual.
      *
      * @param int $months Número de meses que queremos añadir a la fecha actual.
      */
    function addMonths ($months)
    {
        $month = $this->month + $months;
        $year = $this->year;

        while ( !checkdate ($month, 1, $year) )
        {
            $month -= 12;
            $year ++;
        }

        $this->month = intval ($month);
        $this->year  = intval ($year);

        // chequeamos que no pase esto:
        //   date = 31-1
        //   date->addMonths(1) -> 31-2
        // Sol: Elegimos ultimo del mes.
        $day = $this->lastDayOfMonth ();

        if ($this->day > $day)
            $this->day = $day;
    }

    /**
      * Resta el número de meses pasado por parámetro de la fecha actual.
      *
      * @param int $months Número de meses que queremos restar de la fecha actual.
      *
      * @access public
      */
    function substractMonths ($months)
    {
        $day = 1;
        $month = $this->month - intval ($months);
        $year = $this->year;

        while ( !checkdate ($month, $day, $year) )
        {
            $month += 12;
            $year --;
        }

        $this->month = intval ($month);
        $this->year  = intval ($year);

        // chequeamos que no pase esto:
        //   date = 30-3
        //  date->substract_months(1) -> 30-2
        // Sol: Elegimos ultimo del mes.
        $day = $this->lastDayOfMonth ();

        if ( $this->day > $day )
            $this->day = $day;
    }

    /**
      * Añade el número de años pasado por parámetro a la fecha actual.
      *
      * @param int $years Número de años a añadir a la fecha actual.
      *
      * @access public
      */
    function addYears ($years)
    {
        $this->year += intval($years);
        if ( !checkdate ($this->month, $this->day, $this->year) )
        {
              $this->day = 1;
              $this->day = $this->last_day_of_moth();
        }
    }

    /**
      * Resta el número de años pasado por parámetro de la fecha actual.
      *
      * @param int $years Número de años a restar de la fecha actual.
      */
    function substractYears ($years)
    {
        $kk = $this->year - intval ($years);

        if ($i_kk > 0)
        {
            $this->year -= $years;
            if ( !checkdate ($this->month, $this->day, $this->year) )
            {
                $this->day = 1;
                $this->day = $this->last_day_of_moth();
            }
        }
    }

    /**
      * Método para comparar la fecha actual con otra pasada por parámetro.
      *
      * @param object $date Fecha con la que comparar la actual.
      *
      * @return int 0 si las fechas son iguales, -1 si la fecha a comparar es mayor que la actual y 1 si la
      *         fecha actual es mayor que la pasada por parámetro.
      * @access public
      */
    function dateCompare ($date)
    {
        return ( $this->compare ($date->day, $date->month, $date->year) );
    }

    /**
      * Función de comparación de dos fechas.
      *
      * @param int $day Día del mes.
      * @param int $month Número de mes.
      * @param int $year Año con cuatro dígitos.
      *
      * @return int 0 si las fechas son iguales, 1 si la fecha a comparar es mayor que la actual y -1 si la
      *         fecha actual es mayor que la pasada por parámetro.
      *
      * @access private
      */
    function compare ($day, $month, $year)
    {
        // Nos aseguramos que los valores sean numericos.
        $day = intval ($day);
        $month = intval ($month);
        $year = intval ($year);

        if ($this->year < $year)
        {
            return (-1);
        } elseif ($this->year > $year)
          {
            return (1);
          } else
          {
            if ($this->month < $month)
            {
                return (-1);
            }
            elseif ($this->month > $month)
            {
                return (1);
            } else
            {
                if ($this->day < $day)
                {
                    return (-1);
                } elseif ($this->day > $day)
                {
                    return (1);
                }
                else
                {
                return (0);
                }
            }
        }
    }

    /**
      * Devuelve la diferencia en días entre la fecha actual y la pasada por parámetro.
      *
      * @param object $final
      * @return int
      */
    function diffDays ($final)
    {
        $diaIni=$this->timeStamp ();
        $diaFin = $final->timeStamp ();
        $diferencia = $diaFin-$diaIni;
        $dias = $diferencia / 86400;
        return (ceil ($dias) );
    }

    /**
      * Devuelve el último día del mes.
      *
      * @return int
      * @access public
      */
    function lastDayOfMonth ()
    {
        return (intval (date ("t", $this->timeStamp ())));
    }

    /**
      * Devuelve el día de la semana de la fecha actual.
      *
      * @access public
      * @return int
      */
    function dayOfWeek ()
    {
      $day = (intval (date ("w", $this->timeStamp ())));
      return ((0 == $day) ? 7 : $day);
    }

    /**
      * Devuelve el número del día dentro del año.
      *
      * @access public
      * @return int
      */
    function dayOfYear ()
    {
        return (intval (date ("z", $this->timeStamp ())));
    }

    /**
      * Devuelve el día del mes.
      *
      * @access public
      * @return int
      */
    function getDay ()
    {
        return (intval ($this->day));
    }

    /**
      * Devuelve el mes.
      *
      * @access public
      * @return int
      */
    function getMonth ()
    {
      return (intval ($this->month));
    }

    /**
      * Devuelve el año.
      *
      * @access public
      * @return int
      */
    function getYear ()
    {
        return (intval ($this->year));
    }

    /**
      * Devuelve si el año es bisiesto
      *
      * @access public
      * @return boolean
      */
    function isLeapYear ()
    {
        return (intval (date ("L", $this->timeStamp ())));
    }

    /**
      * Devuelve la fecha empaquetada en formato timeStamp
      *
      * @access public
      * @return int
      */
    function timeStamp ()
    {
        $li = mktime (0, 0, 0, $this->month, $this->day, $this->year);
        return (intval ($li));
    }

    /**
      * Devuelve la fecha en formato AAAAMMDD
      *
      * @access public
      * @return string
      */
    function toSql ()
    {
        return (intval (date ("Ymd", $this->timeStamp())));
    }

    /**
      * Fija los datos de la fecha actual a partir de un dato en formato AAAAMMDD.
      *
      * @access public
      */
    function fromSql ($date)
    {
        $par = strval ($date);
        $year = substr ($par, 0, 4);
        $month = substr ($par, 4, 2);
        $day = substr ($par, 6, 2);
        $this->setDate ($day, $month, $year);
    }

    /**
      * Devuelve una string con la forma: "Lunes, "Martes", .....
      *
      * @access public
      * @return string
      */
    function getDayName ($html=1, $language = "es")
    {
        if (1 == $html)
        {
            $dias = array(1 => "Lunes", "Martes", "Mi&eacute;rcoles", "Jueves", "Viernes", "S&aacute;bado", "Domingo");
        }
        else
        {
            $dias = array(1 => "Lunes", "Martes", "Miércoles", "Jueves", "Viernes", "Sábado", "Domingo");
        }

        if ("es" == $language)
        {
            return ($dias[$this->dayOfWeek ()]);
        }
        else
        {
            return (date ("l", $this->timeStamp ()));
        }
    }

    /**
      * Devuelve una string con la forma: "Enero", "Febrero", .....
      *
      * @access public
      * @return string
      */
    function getMonthName ($html=1, $language="es")
    {
        if ("es" == $language)
        {
            $meses = array(1=>"Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre");
            return ($meses[$this->month]);
        }
        else
        {
            return (date ("F", $this->timeStamp ()));
        }
    }


    /**
      * Devuelve una string con la forma:  Dia_semana_, dia_mes de mes de año.
      * Si pasamos un parametro distindo a: "es" Lo devolvemos en ingles:
      * Dia_semana, mes dia(th|nd), año
      *
      * @access public
      * @return string
      */
    function toStr ($html=1, $language = "es", $dayName=1)
    {
        if ("es" == $language)
        {
	    if ($dayName) $kk = $this->getDayName ($html, "es").", ";
            $kk .= $this->day . " de ";
            $kk .= $this->getMonthName ($html, "es");
            $kk .= " de " . $this->year;
            return ($kk);
        }
        else
        {
	    if ($dayName) return (date ("l F dS, Y", $this->timeStamp()));
	    return (date ("F dS, Y", $this->timeStamp()));
        }
    }

    /**
      * Devuelve el mes en formato roman.
      *
      * @access public
      * @return string
      */
    function getMonthRoman ($html=1, $language="es")
    {
        if ("es" == $language)
        {
            $meses = array(1=>"I","II","III","IV","V","VI","VII","VIII","IX","X","XI","XII");
            return ($meses[$this->month]);
        }
        else
        {
            return (date ("F", $this->timeStamp ()));
        }
    }


}
?>
