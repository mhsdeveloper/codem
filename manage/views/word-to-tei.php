<?php  include("manage-head.php");?>
<?php include($_SERVER['DOCUMENT_ROOT'] . "/publications/lib/views/convert-head.php");?>
<script>
	var installDIR = "<? echo \MHS\Env::APP_INSTALL_URL;?>";
	var convertConfig = {
			"processURL": "<? echo \MHS\Env::APP_INSTALL_URL;?><? echo \MHS\Env::CONVERT_PROCESS_URL;?>",
			"dragdropBoxID": "filedrag",
			"dialogBoxID": "actionbox"
	};
</script>
<script src="<? echo \MHS\Env::APP_INSTALL_URL;?>js/vacuum.js"></script>
<style>

	body {
		background: #ffffff;
	}
	
	h1 {
		font-size: 138px;
		margin: 0;
	}
	
	h1 b {
		font-size: 20px;
		vertical-align: baseline;
	}

	.wetvac .nozzle {
		position: relative;
		background: url(<? echo \MHS\Env::APP_INSTALL_URL;?>../images/wetvac-animation1.png) no-repeat;
		background-size: contain;
		height: 331px;
		width: 752px;
	}
	
	.dragDropBox2 {
		padding: 8px;
		text-align: center;
		transition: all .4s ease;
		width: 505px;
		position: absolute;
		bottom: -60px;
		left: 58%;
		margin-left: -29%;
		border: 2px solid black;
		border-radius: 87px;
		height: 100px;
		background: black;
		box-shadow: inset 2px -11px 20px 20px rgb(88, 88, 88);
	}
	
	.dragDropBox2.hover {
		width: 625px;
	    bottom: -94px;
	    left: 49%;
	    height: 131px;
	}
	
	.particle {
		position: absolute;
		width: 5px;
		height: 5px;
		background: black;
		z-index: 100;
	}
	
	
	#particlePoint {
		position: absolute;
		left: 64%;
		top: 87%;
	}
	
	.DialogBox {
		left: 192px !important;
	    top: 56% !important;
		border-radius: 98px;
	    padding: 40px;
	    text-align: center;
		background: white;
	}

	.DialogBox .toolbar {
		display: none;
	}
	
</style>

<script src="<? echo \MHS\Env::APP_INSTALL_URL;?>views/jquery.easing.1.3.js"></script>

</head>
<body>
	<div class="masterc">
		<div class="iwrapper">
			<p>Convert Word .docx files to TEI using the mighty...</p>
			<h1>WET VAC <b>P5000 <i>The "OX"</i></b></h1>
		</div>

		<div class="wetvac">
			<div class="nozzle" id="Nozzle">
				<div id="filedrag" class="dragDropBox2"
					data-status-element-id="xmllist"
					data-post-url="<? echo \MHS\Env::APP_INSTALL_URL;?><? echo \MHS\Env::CONVERT_UPLOAD_URL;?>"
					data-max-file-size="8000000">Drag and drop files here
				</div>
				
				<div id="particlePoint"> </div>
				
				<form class="dropUploaderForm">
				</form>
			</div>

			<iframe id="autoDownload" src=""></iframe>
		</div>
	</div>

</body>
</html>
