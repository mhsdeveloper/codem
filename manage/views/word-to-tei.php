<?php  include("manage-head.php");?>
<?php include($_SERVER['DOCUMENT_ROOT'] . "/publications/lib/views/convert-head.php");?>
<script>

	var convertConfig = {
			"processURL": "<? echo \MHS\Env::APP_INSTALL_URL;?><? echo \MHS\Env::CONVERT_PROCESS_URL;?>",
			"dragdropBoxID": "filedrag",
			"dialogBoxID": "actionbox"
	};


	function Particle(mommy){
		var me = this;
		this.p = document.createElement("div");
		this.p.className = mommy.particleClass;
		mommy.parent.appendChild(this.p);
		
		this.start = function(left, top){
			this.p.style.opacity = "0";
			this.p.style.left = left + "px";
			this.p.style.top = top + "px";
			
			$(this.p).animate({
				opacity: 1,
				top: 0,
				left: 0
			}, 
			mommy.dur * Math.random() + mommy.minDur, 
			function(){
				mommy.restart(me);
			});
		}	
	}

	function Mommy(){
		
		this.dur = 800
		this.minDur = 500
		this.leftOffset = 50;
		this.topOffset = 0;
		this.width = 600;
		this.height = 400;
		this.particleClass = "particle";
		
		this.parent = null;
		
		this.spawn = function(parentEl, number){
			this.parent = parentEl;
		}
		
		this.restart = function(who){
			var w = Math.random() * this.width - this.width * .5;
			var t = Math.random() * this.height;
			who.start(w,t);
		}
	}



	window.addEventListener("DOMContentLoaded", function(){
		jQuery.easing.def = "easeInCubic";
		
		var m = new Mommy();
		
		m.parent = document.getElementById("particlePoint");
		
		var c = 100;
		var p;
		
		for(var i=0; i<c; i++){
			var p = new Particle(m);
			m.restart(p);
		}
		
	});
	
	


</script>

<style>

	body {
		background: #f1f1f1;
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
		background: url(<? echo\MHS\Env::APP_INSTALL_URL;?>images/wetvac.jpg) no-repeat;
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
			<div class="nozzle">
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
