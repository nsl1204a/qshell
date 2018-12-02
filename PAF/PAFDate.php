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
  * @version $Revision: 1.22 $
  * @package PAF
  */
class PAFDate extends PAFObject {
    /**
      * Hora
      * @var int
      * @access private
      */
    var $hour= 0;
	
    /**
      * Minuto
      * @var int
      * @access private
      */
    var $minute= 0;
	
    /**
      * Segundos
      * @var int
      * @access private
      */
    var $second= 0;
	
    /**
      * D�a del mes
      * @var int
      * @access private
      */
    var $day= 0;

    /**
      * Mes del a�o.
      * @var int
      * @access private
      */
    var $month= 0;

    /**
      * A�o
      * @var int
      * @access private
      */
    var $year= -1;

    /**
      * Constructor de la clase.
      * Si no se pasa ning�n par�metro el objeto se inicializa con la fecha actual.
      * Si no es especifica alg�n o algunos de los par�metros asociados se rellenan con los correspondientes
      * a la fecha actual.
      *
      * @param int $day Dia del mes.
      * @param int $month N�mero del mes.
      * @param int $year A�o con cuatro d�gitos.
      *
      * @access public
      */
    function PAFDate ($day=0, $month=0, $year=-1,$hour=-1,$minute=-1,$second=-1)
    {
        // Nos aseguramos que los valores pasados por par�metro son n�meros enteros.
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
			
        if ((-1) == $hour)
            $this->hour = $hoy["hours"];
        else
            $this->hour = $hour;
			
        if ((-1) == $minute)
            $this->minute = $hoy["minutes"];
        else
            $this->minute = $minute;

        // Si no viene por parametro NO tomamos los segundos 
        // de la fecha actual, y se deja a CERO, ya que hay indices
        // que dependen de la funcion timeStamp() sin segundos
        /*
        if ((-1) == $seconds)
            $this->second = $hoy["seconds"];
        else
            $this->second = $second;
        */


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
        $this->hour  = intval ($this->hour);
        $this->minute  = intval ($this->minute);
        $this->second  = intval ($this->second);

		return $this;

    }

    /**
      * Fija la fecha con los valores pasados por par�metro.
      *
      * @param int $day D�a del mes.
      * @param int $month N�mero de mes.
      * @param int $year A�o con cuatro d�gitos.
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
      * A�ade el n�mero de d�as pasados por par�metro a la fecha actual.
      *
      * @param int $days N�mero de d�as que queremos sumar a la fecha actual.
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
      * Resta el n�mero de d�as pasado por par�metro de la fecha actual.
      *
      * @param int $days N�mero de d�as que queremos restar de la fecha actual.
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
      * A�ade el n�mero de semanas pasadas por par�metro a la fecha actual.
      *
      * @param int $weeks N�mero de semanas que queremos a�adir a la fecha actual.
      *
      * @access public
      */
    function addWeeks ($weeks)
    {
        $this->addDays (intval ($weeks) * 7);
    }

    /**
      * Resta el n�mero de semanas pasadas por par�metro de la fecha actual.
      *
      * @param int $weeks N�mero de semanas que queremos restar de la fecha actual.
      *
      * @access public
      */
    function substractWeeks ($weeks)
    {
        $this->substractDays (intval ($weeks) * 7);
    }

    /**
      * A�ade el n�mero de meses pasado por par�metro a la fecha actual.
      *
      * @param int $months N�mero de meses que queremos a�adir a la fecha actual.
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
        $day = $this->lastDayOfMonth (strtotime("$this->year-$this->month-1"));

        if ($this->day > $day)
            $this->day = $day;
    }

    /**
      * Resta el n�mero de meses pasado por par�metro de la fecha actual.
      *
      * @param int $months N�mero de meses que queremos restar de la fecha actual.
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
        $day = $this->lastDayOfMonth (strtotime("$this->year-$this->month-$day"));


        if ( $this->day > $day )
            $this->day = $day;
    }

    /**
      * A�ade el n�mero de a�os pasado por par�metro a la fecha actual.
      *
      * @param int $years N�mero de a�os a a�adir a la fecha actual.
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
      * Resta el n�mero de a�os pasado por par�metro de la fecha actual.
      *
      * @param int $years N�mero de a�os a restar de la fecha actual.
      */
    function substractYears ($years)
    {
        $kk = $this->year - intval ($years);

        if ($kk > 0)
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
      * M�todo para comparar la fecha actual con otra pasada por par�metro.
      *
      * @param object $date Fecha con la que comparar la actual.
      *
      * @return int 0 si las fechas son iguales, -1 si la fecha a comparar es mayor que la actual y 1 si la
      *         fecha actual es mayor que la pasada por par�metro.
      * @access public
      */
    function dateCompare ($date)
    {
        return ( $this->compare ($date->day, $date->month, $date->year) );
    }

    /**
      * Funci�n de comparaci�n de dos fechas.
      *
      * @param int $day D�a del mes.
      * @param int $month N�mero de mes.
      * @param int $year A�o con cuatro d�gitos.
      *
      * @return int 0 si las fechas son iguales, 1 si la fecha a comparar es mayor que la actual y -1 si la
      *         fecha actual es mayor que la pasada por par�metro.
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
      * Devuelve la diferencia en d�as entre la fecha actual y la pasada por par�metro.
      *
      * @param object $final
      * @return int
      */
    function diffDays ($final)
    {
	// Este metodo es independiente de los ajustes de hora de
	// verano o de invierno
    	$diaIni = mktime (12, 0, 0, $this->month, $this->day, $this->year);
    	$diaFin = mktime (12, 0, 0, $final->month, $final->day, $final->year);
	// Diferencia normal de 12 a 12 de la ma�ana
        $diferencia = $diaFin-$diaIni;
        $dias = round($diferencia / 86400);

	$diaIni2 = $this->timeStamp();
	$diaFin2 = $final->timeStamp();
	$diferencia2 = $diaFin2-$diaIni2;
	// Por si hay horas y minutos calculamos la diferencia con horas y minutos
	// $diferencia2 ser� menor que $diferencia si considerendo 
	// las horas la hora inicial es superior a la final y por tanto el ultimo d�a
	// no es completo (le restamos 1)
	if ($diferencia2 < $diferencia) $dias--;
        return ($dias);
    }
	
    /**
      * Devuelve la diferencia en segundos entre la fecha actual y la pasada por par�metro.
      *
      * @param object $final
      * @return int
      */
    function diffSeconds ($final)
    {
        $diaIni=$this->timeStamp ();
        $diaFin = $final->timeStamp ();
        $diferencia = $diaFin-$diaIni;
		
        return ($diferencia);
    }

    /**
      * Devuelve el �ltimo d�a del mes.
      *
      * @return int
      * @access public
      */
    function lastDayOfMonth ($dateStr=null)
    {
        if (!is_null ($dateStr))
            return (intval(date("t", $dateStr)));
        return (intval (date ("t", $this->timeStamp ())));
    }

    /**
      * Devuelve el d�a de la semana de la fecha actual.
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
      * Devuelve el n�mero del d�a dentro del a�o.
      *
      * @access public
      * @return int
      */
    function dayOfYear ()
    {
        return (intval (date ("z", $this->timeStamp ())));
    }

    /**
      * Devuelve los segundos
      *
      * @access public
      * @return int
      */
    function getSecond ()
    {
        return (intval ($this->second));
    }

	/**
      * Devuelve los minutos
      *
      * @access public
      * @return int
      */
    function getMinute ()
    {
        return (intval ($this->minute));
    }

	/**
      * Devuelve las horas
      *
      * @access public
      * @return int
      */
    function getHour ()
    {
        return (intval ($this->hour));
    }

    /**
      * Devuelve el d�a del mes.
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
      * Devuelve el a�o.
      *
      * @access public
      * @return int
      */
    function getYear ()
    {
        return (intval ($this->year));
    }

    /**
      * Devuelve si el a�o es bisiesto
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
        $li = mktime ($this->hour, $this->minute, $this->second, $this->month, $this->day, $this->year);
        return (intval ($li));
    }

    /**
     * Devuelve la fecha en formato AAAAMMDD o en formato AAAMMDDHHMMSS
     *
     * @param boolean $dateTime true devuelve la fecha y la hora
     * @access public
     * @return string
     */
    function toSql ($dateTime=null)
    {
        if ($dateTime)
            return (date ("YmdHis", $this->timeStamp()));
        else
            return (date ("Ymd", $this->timeStamp()));
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
      * Devuelve una string con la forma: "Lunes, "Martes", ..... o "L,M,X" si es short=1
      *
      * @access public
      * @return string
      */
    function getDayName ($html=1, $language = "es",$short=0) {

		if ($short) 
            $dias = array(1=>"L", "M", "X", "J", "V", "S", "D");
		elseif ($html)  
            $dias = array(1=>"Lunes", "Martes", "Mi&eacute;rcoles", "Jueves", "Viernes", "S&aacute;bado", "Domingo");
        else
            $dias = array(1=>"Lunes", "Martes", "Mi�rcoles", "Jueves", "Viernes", "S�bado", "Domingo");

        if ("es" == $language)
            return ($dias[$this->dayOfWeek ()]);
        else
            return (date ("l", $this->timeStamp ()));
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
      * Devuelve una string con la forma:  Dia_semana_, dia_mes de mes de a�o.
      * Si pasamos un parametro distindo a: "es" Lo devolvemos en ingles:
      * Dia_semana, mes dia(th|nd), a�o
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


    /**
      * Fija la hora con los valores pasados por par�metro.
      *
      * @param int $hours Hora
      * @param int $minutes Minutos
      * @param int $seconds Segundos 
      *
      * @access public
      */
    function setTime($hours, $minutes, $seconds=0 ) {
        $hours = intval($hours);
        $minutes = intval($minutes);
        $seconds = intval($seconds);

        if ($hours >=0 && $hours <=24) 
           $this->hour = $hours;
        if ($minutes >=0 && $minutes <60) 
            $this->minute = $minutes;
        if ($seconds >=0 && $seconds <60) 
           $this->second =$seconds;
    }

    /**
      * Fija los datos de la fecha actual a partir de un dato en formato timestamp.
      *
      * @access public
      */
    function fromTimestamp($timestamp) {
        $date = getdate($timestamp);
        $this->setDate($date["mday"], $date["mon"], $date["year"]);
        $this->setTime($date["hours"], $date["minutes"], $date["seconds"]);
    }
}
?>
