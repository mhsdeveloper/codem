

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
				top: 80,
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


	//preload
	var x = 1;
	var images = [];
	var i;
	for( ; x <= 20; x++){
		i = new Image();
		i.src = "" + installDIR + "../images/wetvac-animation" + x + ".png";
		images.push(i);
	}
	var nozzle;
	var startTime, time, frame;
	var duration = 1500; // = 2 sec
	var lastframe = 0;
	var frames = [
		"url(" + installDIR + "../images/wetvac-animation1.png)",
		"url(" + installDIR + "../images/wetvac-animation2.png)",
		"url(" + installDIR + "../images/wetvac-animation3.png)",
		"url(" + installDIR + "../images/wetvac-animation4.png)",
		"url(" + installDIR + "../images/wetvac-animation5.png)",
		"url(" + installDIR + "../images/wetvac-animation6.png)",
		"url(" + installDIR + "../images/wetvac-animation7.png)",
		"url(" + installDIR + "../images/wetvac-animation8.png)",
		"url(" + installDIR + "../images/wetvac-animation9.png)",
		"url(" + installDIR + "../images/wetvac-animation10.png)",
		"url(" + installDIR + "../images/wetvac-animation11.png)",
		"url(" + installDIR + "../images/wetvac-animation12.png)",
		"url(" + installDIR + "../images/wetvac-animation13.png)",
		"url(" + installDIR + "../images/wetvac-animation14.png)",
		"url(" + installDIR + "../images/wetvac-animation15.png)",
		"url(" + installDIR + "../images/wetvac-animation16.png)",
		"url(" + installDIR + "../images/wetvac-animation17.png)",
		"url(" + installDIR + "../images/wetvac-animation18.png)",
		"url(" + installDIR + "../images/wetvac-animation18.png)",
		"url(" + installDIR + "../images/wetvac-animation19.png)",
		"url(" + installDIR + "../images/wetvac-animation19.png)",
		"url(" + installDIR + "../images/wetvac-animation19.png)",
		"url(" + installDIR + "../images/wetvac-animation20.png)",
		"url(" + installDIR + "../images/wetvac-animation20.png)",
		"url(" + installDIR + "../images/wetvac-animation20.png)",
		"url(" + installDIR + "../images/wetvac-animation20.png)",
		"url(" + installDIR + "../images/wetvac-animation20.png)",
		"url(" + installDIR + "../images/wetvac-animation18.png)",
		"url(" + installDIR + "../images/wetvac-animation18.png)",
		"url(" + installDIR + "../images/wetvac-animation18.png)",
		"url(" + installDIR + "../images/wetvac-animation19.png)",
		"url(" + installDIR + "../images/wetvac-animation19.png)",
		"url(" + installDIR + "../images/wetvac-animation19.png)",
		"url(" + installDIR + "../images/wetvac-animation20.png)",
		"url(" + installDIR + "../images/wetvac-animation1.png)",
	];
	
	var run = function() {
		time = new Date().getTime() - startTime;
		frame = Math.floor(frames.length * (time / duration));
		if(frame < frames.length) {
		  	requestAnimationFrame(run);
			if(frame < frames.length){
				nozzle.style.backgroundImage = frames[frame];
				lastframe = frame;
			}
		}
		else {
			nozzle.style.backgroundImage = "url(" + installDIR + "../images/wetvac-animation1.png)";
			
			DB.open();
			DB.title.textContent = "Processing";

		}
	}


	var DB;
	
	window.addEventListener("DOMContentLoaded", function(){

		DB = new DialogBox();
		DB.init(convertConfig.dialogBoxID);


		var Uploader = new PubToolsUploader();

		Uploader.responseHandler = function(resObj, e){
			DB.content.innerHTML = "Processing " + resObj.filename + ".<br/><br/>\n";
			startTime = new Date().getTime();
			run();

			if (typeof resObj.errors !== "undefined"){
				if(typeof resObj.errors == "string") var li = resObj.errors;
				else var li = resObj.errors.join("</li>\n<li>");

				li = "<li>" + li + "</li>\n";
				DB.open(li, e);
				DB.title.textContent = "Error";
			} else {
				
				if(typeof resObj.filename == "undefined") {
					DB.open("Error, no filename returned; cannot continue.", e);
					DB.title.textContent = "Error";
					return;
				}
				
//				DB.open("Uploaded " + resObj.filename + "<br/>Now processing...", e);
//				DB.title.textContent = "Processing";
				
				var f = resObj.filename;
				
				callConverter(f,e);
			}
		}


		function callConverter(f, e){
			$.ajax({
				dataType: "json",
				method: "get",
				data: {filename: f},
				url: convertConfig.processURL,
				success: function(json){

					if(json.status == "download"){

						DB.content.innerHTML += " done.<br/>";

						var link = '<a href="' + json.filename + '" download >Click to download TEI XML file</a>';
						
						DB.content.innerHTML += "<br/>" + link;
						DB.title.textContent = "Download TEI file";
					} else if(typeof json.errors != "undefined") {
						
						DB.content.innerHTML += " error.<br/>Sorry there was an error: " + json.errors;
					}
				}
			});
		}

	
		Uploader.init(document.getElementById(convertConfig.dragdropBoxID));

		
		
		
		
		jQuery.easing.def = "easeInCubic";
		
		var m = new Mommy();
		
		m.parent = document.getElementById("particlePoint");
		
		var c = 350;
		var p;
		
		for(var i=0; i<c; i++){
			var p = new Particle(m);
			m.restart(p);
		}
		
		nozzle = document.getElementById("Nozzle");
		document.getElementById("filedrag").addEventListener("click", function(){
		});
	});
	
	
