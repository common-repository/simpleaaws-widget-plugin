var iexplore = document.all?true:false;
var pos_mouseX = 0;
var pos_mouseY = 0;

function getMouseXY(e) 
{
  if (iexplore || !e)
    e = window.event;
    
  pos_mouseX = e.screenX;
  pos_mouseY = e.screenY;
  
   if (pos_mouseX < 0) 
    pos_mouseX = 0;
  if (pos_mouseY < 0) 
    pos_mouseY = 0;
  
  return true;
}

if (!iexplore)
{
  document.captureEvents(Event.MOUSEMOVE);
  document.captureEvents(Event.MOUSEDOWN);
  document.captureEvents(Event.MOUSEUP);
};

document.onmousemove = getMouseXY;