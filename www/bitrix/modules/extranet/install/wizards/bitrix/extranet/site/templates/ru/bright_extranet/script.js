function SetPrintCSS(isPrint)
{
	var link;

	if (document.getElementsByTagName)
		link = document.getElementsByTagName('link');
	else if (document.all)
		link = document.all.tags('link');
	else
		return;

	for (var index=0; index < link.length; index++)
	{
		if (!link[index].title || link[index].title != 'print')
			continue;

		if (isPrint)
		{
			link[index].disabled = false;
			link[index].rel = "stylesheet";
		}
		else
		{
			link[index].disabled = true;
			link[index].rel = "alternate stylesheet";
		}
	}
}

function AddToBookmark()
{
	var title = window.document.title;
	var url = window.document.location;

	if (window.sidebar)
	{
		window.sidebar.addPanel(title, url, "");
	}
	/*else if(window.opera)
	{
		var a = document.createElement("A");
		a.rel = "sidebar";
		a.target = "_search";
		a.title = title;
		a.href = url;
		a.click();
	}*/
	else if(document.all)
	{
		window.external.AddFavorite(url, title);
	}
	else
	{
		alert("Для добавления страницы в Избранное нажмите Ctrl+D");
	}
	
	return false;
}

function BackToDesignMode()
{
	if (document.all)
		window.location.href = window.location.href.replace('#print','');
	else
		SetPrintCSS(false);

	return false;
}

if (document.location.hash == '#print')
	SetPrintCSS(true);

if (window.jsAjaxUtil)
{
	// show ajax visuality
	jsAjaxUtil.ShowLocalWaitWindow = function (TID, cont, bShadow)
	{
		if (typeof cont == 'string' || typeof cont == 'object' && cont.constructor == String)
			var obContainerNode = document.getElementById(cont);
		else
			var obContainerNode = cont;
		
		if (obContainerNode.getBoundingClientRect)
		{
			var obRect = obContainerNode.getBoundingClientRect();
			var obWndSize = jsAjaxUtil.GetWindowSize();

			var arContainerPos = {
				left: obRect.left + obWndSize.scrollLeft, 
				top: obRect.top + obWndSize.scrollTop, 
				right: obRect.right + obWndSize.scrollLeft, 
				bottom: obRect.bottom + obWndSize.scrollTop
			};
		}
		else
			var arContainerPos = jsAjaxUtil.GetRealPos(obContainerNode);
		
		var container_id = obContainerNode.id;
		
		if (!arContainerPos) return;

		var obWaitMessage = document.body.appendChild(document.createElement('DIV'));
		obWaitMessage.id = 'wait_' + container_id + '_' + TID;
		obWaitMessage.className = 'waitwindow';
		
		var div_top = arContainerPos.top + 5;
		if (div_top < document.body.scrollTop) div_top = document.body.scrollTop + 5;
		
		obWaitMessage.style.top = div_top + 'px';
		obWaitMessage.style.left = (arContainerPos.right - 200) + 'px';
		obWaitMessage.innerHTML = 'Загрузка...';
		
		if(jsAjaxUtil.IsIE())
		{
			var frame = document.createElement("IFRAME");
			frame.src = "javascript:''";
			frame.id = 'waitframe_' + container_id + '_' + TID;
			frame.className = "waitwindow";
			frame.style.width = obWaitMessage.offsetWidth + "px";
			frame.style.height = obWaitMessage.offsetHeight + "px";
			frame.style.left = obWaitMessage.style.left;
			frame.style.top = obWaitMessage.style.top;
			document.body.appendChild(frame);
		}
		
		function __Close(e)
		{
			if (!e) e = window.event
			if (!e) return;
			if (e.keyCode == 27)
			{
				jsAjaxUtil.CloseLocalWaitWindow(TID, cont);
				jsEvent.removeEvent(document, 'keypress', __Close);
			}
		}
		
		jsEvent.addEvent(document, 'keypress', __Close);
	}
}