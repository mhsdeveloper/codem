<?php  include("manage-head.php");?>

<script>
	
	var convertConfig = {
			"processURL": "<? echo \MHS\Env::APP_INSTALL_URL;?><? echo \MHS\Env::CONVERT_PROCESS_URL;?>",
			"dragdropBoxID": "filedrag",
			"dialogBoxID": "actionbox"
	};

</script>	
	
<?php include($_SERVER['DOCUMENT_ROOT'] . "/publications/lib/views/convert-head.php");?>

</head>
<body>
	
	<?php include("manage-header.php") ; ?>
	<div class="masterc">
		<div class="iwrapper">
			<h1>Convert Word to TEI</h1>
		</div>
	
		<?php include($_SERVER['DOCUMENT_ROOT'] . "/publications/lib/views/convert-template.php");?>
	
	</div>
	
</body>
</html>