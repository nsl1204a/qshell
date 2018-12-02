<HTML>
<HEAD>
<TITLE>PRUEBA DE OUTPUTS</TITLE>
<LINK rel='STYLESHEET' type='text/css' href='stilos.css'>
</HEAD>
<BODY>
    <CENTER>
        <TABLE border="1" width="60%" align="MIDDLE">
           <TR>
              <TD align="middle" class="titTabla"> <b> <!-- {TITULO1} --> </b> </TD>
              <TD align="middle" class="titTabla"> <b> <!-- {TITULO2} --> </b> </TD>
              <TD align="middle" class="titTabla"> <b> <!-- {TITULO3} --> </b> </TD>
           </TR>
            <!-- @ FILAS @ -->
           <TR>
              <TD class="tTxtTabla"> <!-- {CAMPO1} -->  </TD>
              <TD class="tTxtTabla"> <!-- {CAMPO2} -->  </TD>
              <TD class="tTxtTabla"> <!-- {CAMPO3} -->  </TD>
           </TR>
           <!-- @ FILAS @ -->
           <TR>
              <TD colspan="3" class="titTabla">
                 <b>Número de Registros recuperados:</b> <!-- {NUMREG} -->
              </TD>
           </TR>
           <TR>
              <TD colspan="3" class="titTabla">
                 <b>Tiempo de ejecución del query:</b> <!-- {PROFILEQUERY} --> (segundos)
              </TD>
           </TR>
           <TR>
              <TD colspan="3" class="titTabla">
                 <b>Tiempo Total de ejecución:</b> <!-- {PROFILETOTAL} --> (segundos)
              </TD>
           </TR>
        </TABLE>
    </CENTER>
</BODY>
</HTML>