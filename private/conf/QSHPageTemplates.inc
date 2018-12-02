<?php
/**
 * Plantillas htm de la pagina
 */

$htmlPage = <<<EOD
		<!DOCTYPE html>
		<html dir="ltr" lang="es">
		<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		
		<title>QSMenu</title>
		<base href=".">
		<link rel="stylesheet" type="text/css"
			href="/static/css/jquery-ui-1.8.6-osc.css">
		<!--	
		<link rel="icon" type="image/png" href="http://livedemo00.template-help.com/osc_38758/images/icons/favicon.ico">
		-->
		
		<link rel="stylesheet" type="text/css"
			href="/static/css/960_24_col.css">
		<link rel="stylesheet" type="text/css"
			href="/static/css/stylesheet.css">
		<link rel="stylesheet" type="text/css"
			href="/static/css/constants.css">
		<link rel="stylesheet" type="text/css"
			href="/static/css/style.css">
		<link rel="stylesheet" type="text/css"
			href="/static/css/style_boxes.css">
		<link rel="stylesheet" type="text/css"
			href="/static/css/css3.css">
		<link rel="stylesheet" type="text/css"
			href="/static/css/buttons.css">
		<link rel="stylesheet" type="text/css"
			href="/static/css/nivo-slider.css">
		<link rel="stylesheet" type="text/css" media="screen"
			href="/static/css/animations.css">
		<link rel="stylesheet"
			href="/static/css/stringball.css" type="text/css">

		
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
		     <a href="http://windows.microsoft.com/en-US/internet-explorer/products/ie/home?ocid=ie6_countdown_bannercode">
		       <img src="http://storage.ie6countdown.com/assets/100/images/banners/warning_bar_0000_us.jpg"  alt="You are using an outdated browser. For a faster, safer browsing experience, upgrade for free today." />
		    </a>
		  </div>
		<![endif]-->
		<style type="text/css">
		.nivoSlider_wrapper {
			width: 950px;
			height: 486px; /* Set the size */
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
		<body>
			<p id="back-top" style="visibility: visible; display: none;">
				<a href="http://livedemo00.template-help.com/osc_38758/#top"><span>Top</span>
				</a>
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
		
						<!--</div>-->
					</div>
				</div>
		
				<div class="row_4 ofh">
					<div class="container_24">
						<div id="bodyContent" class="grid_18 push_6 ">
							<div style="width: 1000px;">
								@@contenidos@@
							</div>	
						</div>
						<!-- bodyContent //-->
						<div id="columnLeft" class="grid_6 pull_18">@@appMenu@@</div>
					</div>
				</div>
			</div>
			<a class="logo"	href=""><img src="/static/css/store_logo.png" alt="QShell" title=" QShell " width="356" height="37"></a>
			<!--	 bodyWrapper //-->
		
		
			<!--[if lt IE 9]>
		      <link href="css/ie_style.css" rel="stylesheet" type="text/css" />
		    <![endif]-->
		
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
						id="fancybox-left-ico"></span>
					</a><a href="javascript:;" id="fancybox-right"><span
						class="fancy-ico" id="fancybox-right-ico"></span>
					</a>
				</div>
			</div>
		</body>
		</html>
EOD;


$qsh_global_template = array(

	'gobalPage' => $htmlPage,

	'appMenu' => '
		<div>
			<div class="infoBoxWrapper list first">
				<div class="box_wrapper">
					<div class="infoBoxHeading">
						<div class="box_wrapper_title">
							<h1>
								<span class="title-icon"></span>@@appname@@
							</h1>
						</div>
					</div>
					<div class="infoBoxContents">
						<ul class="categories">
							<!--  por cada funcion -->
							@@functions@@
						</ul>
					</div>
				</div>
			</div>
			<div class="infoBoxWrapper box3 last"></div>
		</div>',

	'funcMenu' => '
		<li class="htooltip first"><div class="div_2">
				<a
					href="@@funclink@@"><div
						class="div_2">
						<div class="list_bg"></div>
						@@funcname@@
					</div> </a>
			</div>
		</li>',

	'ariadna' => ' 
		<div class="breadcrumb">
			&nbsp;&nbsp;<span>@@appname@@</span> > <span>@@funcname@@</span>
		</div>',

	'notice' => '
		<div class="box_wrapper_title">
		<span class="title-icon"></span>
		<h1>@@notice@@</h1>
		</div>',

	'content' => '
		<div class="breadcrumb">
				@@content@@
		</div>',


	'frmlogion' =>
 '	<div class="box_wrapper_title">
		<span class="title-icon"></span>
		<h1>@@notice@@</h1>
	</div>
	<div class="breadcrumb">
			<form action="login.php" name="FormLogin" method="POST">
				<hr>
				<table border="0">
					<tbody>
						<tr>
							<td><b>Usuario:</b></td>
							<td><input type="text" maxlength="20" size="20" value="" name="username"></td>
						</tr>
						<tr>
							<td><b>Contrase&ntilde;a:</b></td>
							<td><input type="password" maxlength="20" size="20" value="" name="password"></td>
						</tr>
						<tr>
						</tr>
					</tbody>
				</table>
				<hr>
				<input type="submit" value="Aceptar" />
			</form>
	</div>'


);

?>