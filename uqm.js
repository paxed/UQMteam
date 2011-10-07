
var off_x=30;
var off_y=30;

var cid=document.getElementById('extra_info');
var isvis=false;

function mouseX(evt) {
  if(!evt)
    evt=window.event; 
  if(evt.pageX) 
    return evt.pageX; 
  else if(evt.clientX)
    return evt.clientX+(document.documentElement.scrollLeft?document.documentElement.scrollLeft:document.body.scrollLeft); 
  else
    return 0;
}

function mouseY(evt) {
  if(!evt) 
    evt=window.event; 
  if(evt.pageY) 
    return evt.pageY; 
  else if(evt.clientY)
    return evt.clientY+(document.documentElement.scrollTop?document.documentElement.scrollTop:document.body.scrollTop); 
  else
    return 0;

}




function follow(evt) {
  if (document.getElementById&&isvis==true) {
    var sx, sy, x, y;

    if(document.documentElement && document.documentElement.clientHeight) {
      sx=document.documentElement.clientWidth;
      sy=document.documentElement.clientHeight;
    } else if(self.innerHeight) {
      sx=self.innerWidth;
      sy=self.innerHeight;
    } else if(document.body) {
      sx=document.body.clientWidth;
      sy=document.body.clientHeight;
    } else {
      sx=0;
    }

    var dx=cid.clientWidth;
    var dy=cid.clientHeight;

    var mx=parseInt(mouseX(evt));
    var my=parseInt(mouseY(evt));

    x=mx+off_x;
    if(x+dx>sx)
      x=mx-off_x-dx;

    y=my+off_y;
    if(y+dy>(sy+document.documentElement.scrollTop))
      y=my-off_y-dy;

    cid.style.left=(x)+'px';
    cid.style.top=(y)+'px';

    if(sx&&mx&&dx) {
      if(cid.style.visibility!='visible')
	cid.style.visibility='visible';
    }
  }
}

document.onmousemove=follow;


function show_tag(txt) {
  if(arguments.length>1) {
    cid.style.opacity=1;
    if (typeof cid.style.filter != 'undefined') {
      cid.style.filter='alpha(opacity=100)';
      /*    cid.style.filter.alpha.opacity=100;*/
    }
  } else {
    cid.style.opacity=0.8;
    if (typeof cid.style.filter != 'undefined') {
      cid.style.filter='alpha(opacity=80)';
      /*    cid.style.filter.alpha.opacity=80;*/
    }
  }
  cid.innerHTML=txt;
  cid.style.visibility='visible';
  isvis=true;
}

function hide_tag() {
  cid.style.visibility='hidden';
  isvis=false;
}

function turn_on(x,y) {
  if (!x.checked) {
    x.checked=true;
    y.style.opacity=1;
    if (typeof cid.style.filter != 'undefined') {
      y.style.filter='alpha(opacity=100)';
      /*y.style.filter.alpha.opacity=100;*/
    }
  }
  hide_urls();
}

function toggle(x,y) {
  if (x.checked) {
    x.checked=false;
    y.style.opacity=0.50;
    if (typeof y.style.filter != 'undefined') {
      y.style.filter='alpha(opacity=0.50)';
      /*y.style.filter.alpha.opacity=50;*/
    }
  } else {
    x.checked=true;
    y.style.opacity=1;
    if (typeof cid.style.filter != 'undefined') {
      y.style.filter='alpha(opacity=100)';
      /*y.style.filter.alpha.opacity=100;*/
    }
  }
  hide_urls();
}

function hide_urls() {
  for (var i=0; i < document.links.length; i++) {
    if (document.links[i].className == 'volatile') {
      document.links[i].style.visibility='hidden';
    }
  }
  var i = document.getElementById('press_here');
  i.innerHTML = '&lt;&mdash; Click here to update the teams';
}

function changeallowed_rnd(s) {
  var ds = document.getElementById(s);
  for (var i=0; i < ds.childNodes.length; i++) {
    if ((ds.childNodes[i].tagName == 'IMG') && (Math.random() < 0.5)) {
      toggle(ds.childNodes[i].nextSibling.childNodes[0], ds.childNodes[i]);
    }
  }
}

function changeallowed_toggle(s) {
  var ds = document.getElementById(s);
  for (var i=0; i < ds.childNodes.length; i++) {
    if (ds.childNodes[i].tagName == 'IMG') {
      toggle(ds.childNodes[i].nextSibling.childNodes[0], ds.childNodes[i]);
    }
  }
}

function changeallowed_set(s) {
  var ds = document.getElementById(s);
  for (var i=0; i < ds.childNodes.length; i++) {
    if (ds.childNodes[i].tagName == 'IMG') {
      turn_on(ds.childNodes[i].nextSibling.childNodes[0], ds.childNodes[i]);
    }
  }
}