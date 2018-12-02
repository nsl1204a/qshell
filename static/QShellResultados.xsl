<?xml version="1.0"?>
<xsl:stylesheet
	version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns="http://www.w3.org/1999/xhtml">

<xsl:output method="html"/>
<xsl:template match="QSResultados">

	<html dir="ltr" lang="en">
	<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	
	<title>QSResultados</title>
	<base href="." />
	<!--<link rel="stylesheet" type="text/css"-->
		<!--href="/static/css/jquery-ui-1.8.6-osc.css" />-->
	<!--<script type="text/javascript" src="/static/css/jquery-1.4.3.min.js"></script>-->
	<!--&lt;!&ndash;  &ndash;&gt;-->
	<!--<script type="text/javascript" src="/static/css/jquery-ui-1.8.6.min.js"></script>-->
	<!---->
	<!--<script type="text/javascript"-->
		<!--src="/static/css/jquery.bxGallery.1.1.min.js">-->
	<!--</script>-->
	<!--<link rel="stylesheet" type="text/css"-->
		<!--href="/static/css/jquery.fancybox-1.3.4.css" />-->
	<!--<script type="text/javascript"-->
		<!--src="/static/css/jquery.fancybox-1.3.4.pack.js"></script>-->
	<!--<script type="text/javascript" src="/static/css/xeasyTooltipIMG.js"></script>-->
	<!--<script type="text/javascript" src="/static/css/jquery.equalheights.js"></script>-->
	<!--<script type="text/javascript" src="/static/css/jquery.nivo.slider.js"></script>-->
	<!--<script type="text/javascript" src="/static/css/jquery.jqtransform.js"></script>-->
	<!--<script type="text/javascript" src="/static/css/jquery.stringball.js"></script>-->
	
	<link rel="stylesheet" type="text/css" href="/static/css/960_24_col.css" />
	<link rel="stylesheet" type="text/css" href="/static/css/stylesheet.css" />
	<link rel="stylesheet" type="text/css" href="/static/css/constants.css" />
	<link rel="stylesheet" type="text/css" href="/static/css/style.css" />
	<link rel="stylesheet" type="text/css" href="/static/css/style_boxes.css" />
	<link rel="stylesheet" type="text/css" href="/static/css/css3.css" />
	<link rel="stylesheet" type="text/css" href="/static/css/buttons.css" />
	<link rel="stylesheet" type="text/css" href="/static/css/nivo-slider.css" />
	<link rel="stylesheet" type="text/css" media="screen" href="/static/css/animations.css" />
	<link rel="stylesheet" href="/static/css/stringball.css" type="text/css" />
	<link rel="stylesheet" href="static/css/rowcol.css" type="text/css" />
	<script type="text/javascript" src="/static/css/js.js"></script>
	<script type="text/javascript">
		$(document).ready(function() {
			$('.custom_select form').jqTransform({
				imgPath : 'jqtransformplugin/img/'
			});
		});
	</script>
	
	<!--[if lt IE 9]>
	<style type="text/css">
	.cart_products_options,
	.contentPadd.txtPage,
	.ui-dialog,
	.ui-dialog-titlebar,
	.cart,
	.ui-progressbar,
	.ui-datepicker,
	 
	.contentInfoText.un, 
	.contentInfoBlock,
	.listing_padd,
	
	.cart_products_options,
	.row_7 CHECKBOX, .row_7 INPUT, .row_7 RADIO, .row_7 select, .row_7 textarea,
	.fieldValue  input, .go, .input,
	.contentPadd h3,
	.cart th.th1,
	.contentInfoText,
	.cart th.th3,
	.title-t,
	.navbar_bg,
	.infoBoxContents,
	.contentInfoText 
	.contentInfoBlock,
	.sf-menu ul ul,
	.div_cat_navbar,
	.sf-menu a.sf-with-ul .wrapper_level,
	.bg_button .button-b,
	.contentPadd,
	.sf-menu > li > a,
	.infoHeading,
	.infoBoxHeading,
	.footer,
	.button_content2 .bg_button .button-t,
	.button_content2 .bg_button:hover .button-t,
	.button_content2 .bg_button.act .button-t,
	.button_content22 .bg_button .button-t,
	.button_content22 .bg_button:hover .button-t,
	.button_content22 .bg_button.act .button-t,
	.custom_select ul,
	.box_wrapper_title
	   { behavior:url(/osc_38758/ext/pie/PIE.php)}
	</style>
	 <![endif]-->
	<!--[if lt IE 8]>
	   <div style=' clear: both; text-align:center; position: relative;'>
	  </div>
	<![endif]-->
	
	<style type="text/css">
	.nivoSlider_wrapper {
		width: 950px;
		height: 486px; /* Set the size */
	}
	
	#screenshot img {
		width: SUPERFISH_IMAGE_WIDTHpx;
		height: SUPERFISH_IMAGE_HEIGHTpx;
	}
	
	#screenshotCategory img {
		width: 80px;
		height: 55px;
	}
	</style>
	<style type="text/css">
	.nivo-caption {
		display: none !important;
	}
	</style>
	</head>


<!-- ANTIGUO HEAD  
 <html>
      <head>
      		<link rel="stylesheet" type="text/css" href="static/css/rowcol.css" />
      </head>
 -->      
 
 
 	<body>
		<p id="back-top" style="visibility: visible; display: none;">
			<a href=""><span>Top</span></a>
		</p>
		<!-- bodyWrapper  -->
		<div id="bodyWrapper" class="bg_body">
	
			<div class="row_1">
				<div class="container_24">
					<!--<div class="grid_24">-->
					<div id="header">
	
	
						<div class="box_header_cart destination">
							<div class="cart_header">
								<div>
									<span></span>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="row_4 ofh">
				<div class="breadcrumb">
					<span><xsl:value-of select="DesAplicacion"/></span> > <span><xsl:value-of select="DesFuncion"/></span>
				</div>',

				<p align="center">
					<xsl:if test="Mensaje!=''">
						<xsl:value-of select="Mensaje"/>
					</xsl:if>
				</p>
				<hr />	 
				<ol><xsl:apply-templates select="Bloque"/></ol>
			</div>
		</div>
		
		<a class="logo"	href=""><img src="/static/css/store_logo.png" alt="QShell" title=" QShell " width="356" height="37" /></a>		
		<!--	 bodyWrapper //-->
	
	
		<!--[if lt IE 9]>
	      <link href="css/ie_style.css" rel="stylesheet" type="text/css" />
	    <![endif]-->
		<script type="text/javascript" src="/static/css/imagepreloader.js"></script>
		<script type="text/javascript">
			preloadImages([
			//	'images/user_menu.gif',
			'images/bg_list.png', 'images/hover_bg.png', 'images/nivo-nav.png',
			// 'images/nivo-nav.png',									
			'images/menu_item-act.gif', 'images/wrapper_pic.png',
					'images/wrapper_pic-act.png' ]);
		</script>

		<div id="fancybox-tmp"></div>
		<div id="fancybox-loading">
			<div></div>
		</div>
		<div id="fancybox-overlay"></div>
		<div id="fancybox-wrap">
			<div id="fancybox-outer">
				<div class="fancybox-bg" id="fancybox-bg-n"></div>
				<div class="fancybox-bg" id="fancybox-bg-ne"></div>
				<div class="fancybox-bg" id="fancybox-bg-e"></div>
				<div class="fancybox-bg" id="fancybox-bg-se"></div>
				<div class="fancybox-bg" id="fancybox-bg-s"></div>
				<div class="fancybox-bg" id="fancybox-bg-sw"></div>
				<div class="fancybox-bg" id="fancybox-bg-w"></div>
				<div class="fancybox-bg" id="fancybox-bg-nw"></div>
				<div id="fancybox-content"></div>
				<a id="fancybox-close"></a>
				<div id="fancybox-title"></div>
				<a href="javascript:;" id="fancybox-left"><span class="fancy-ico"
					id="fancybox-left-ico"></span> </a><a href="javascript:;"
					id="fancybox-right"><span class="fancy-ico"
					id="fancybox-right-ico"></span> </a>
			</div>
		</div>
	</body>
</html>

</xsl:template>
<xsl:template match="Bloque">
   <li>
		<h2><b><xsl:value-of select="DesBloque"/></b> 
		(<xsl:value-of select="CodBloque"/>)</h2>
		<xsl:if test="Cambios!=''">
			<xsl:if test="Cambios!='0'">
				<br />Numero de cambios realizados: <xsl:value-of select="Cambios"/>
			</xsl:if>
		</xsl:if>
		
		<xsl:if test="Mensaje!=''">
			<!--
			<div class="box_wrapper_title">
			-->
				<span class="title-icon"></span>
				<h1><xsl:value-of select="Mensaje"/></h1>
			<!--	
			</div>
			-->
		</xsl:if>	

		<!--
		<b>PARAMETROS:</b>
		-->

	   <table BORDER="0">
	   	<tbody>
	   	   <tr>	
			   <td>PARAMETROS DE ENTRADA</td>
			   <td>_____________________</td>
			   <td>PARAMETROS DE SALIDA</td>
		   </tr>
		   <tr>
				<td>	
					  <table>
						<xsl:apply-templates select="ParametroI"/>
					  </table>
				</td>
				<td></td>
				<td>	
					  <table>
						<xsl:apply-templates select="ParametroO"/>
					   </table>
				 </td>
			</tr>
			</tbody>  
		</table>
		
		<xsl:if test="Filas!=''">
			<br/>
			<b>NUMERO DE FILAS RESULTANTES: <xsl:value-of select="Filas"/></b>
			<br/>
		</xsl:if>
		<xsl:if test="Filas!='0'">
		<!--
			<b>RESULTADOS:</b>
		-->
		   <table BORDER="0">

				<xsl:apply-templates select="Cabecera"/>
	
				<xsl:apply-templates select="FilaResultado"/>

		   </table>
		</xsl:if>			   
						
		<hr />	  
  </li>
</xsl:template>
<xsl:template match="ParametroI">
	<tr>
	  <xsl:attribute name="class">
		<xsl:choose>
		  <xsl:when test="position() mod 2 = 1">rowodd</xsl:when>
		  <xsl:when test="position() mod 2 = 0">roweven</xsl:when>
		</xsl:choose>
	  </xsl:attribute>	
	<td valign="top">
	<xsl:value-of select="Nombre" />:	<xsl:value-of select="Valor" />
	</td>
	</tr>

</xsl:template>
<xsl:template match="ParametroO">
	<tr>
	  <xsl:attribute name="class">
		<xsl:choose>
		  <xsl:when test="position() mod 2 = 1">rowodd</xsl:when>
		  <xsl:when test="position() mod 2 = 0">roweven</xsl:when>
		</xsl:choose>
	  </xsl:attribute>
	<td valign="top">
	<xsl:value-of select="Nombre" />:	<xsl:value-of select="Valor" />
	</td>
	</tr>
</xsl:template>
<xsl:template match="Cabecera">

		<th>
		<u><xsl:value-of select="." /></u>
		</th>

	<!-- <th>__</th> -->
</xsl:template>
<xsl:template match="FilaResultado">
	<tr>
	  <xsl:attribute name="class">
		<xsl:choose>
		  <xsl:when test="position() mod 2 = 1">rowodd</xsl:when>
		  <xsl:when test="position() mod 2 = 0">roweven</xsl:when>
		</xsl:choose>
	  </xsl:attribute>
		<xsl:apply-templates select="Columna"/>
	</tr>
</xsl:template>
<xsl:template match="Columna">
	<td>
	    <xsl:choose>
	      <xsl:when test="Enlace!=''">
	           	<a href="{Enlace}"><xsl:value-of select="Valor" /></a>
	      </xsl:when>
	      <xsl:otherwise>
		        <xsl:value-of select="Valor" />
	      </xsl:otherwise>
	      
	    </xsl:choose>
	</td>
</xsl:template>

</xsl:stylesheet>
