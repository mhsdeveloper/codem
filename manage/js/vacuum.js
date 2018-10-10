

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
		
		nozzle = document.getElementById("Nozzle");
		document.getElementById("filedrag").addEventListener("click", function(){
			startTime = new Date().getTime();
			run();
		});
	});
	
	
