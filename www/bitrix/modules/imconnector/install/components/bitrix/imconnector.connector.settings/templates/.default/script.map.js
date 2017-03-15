{"version":3,"file":"script.min.js","sources":["script.js"],"names":["window","destInput","id","node","inputName","this","appendChild","BX","create","props","className","html","join","defer_proxy","bind","prototype","nodes","inputBox","input","container","button","proxy","search","searchBefore","e","SocNetLogDestination","openDialog","PreventDefault","onChangeDestination","addCustomEvent","select","unSelect","delete","closeDialog","closeSearch","item","el","prefix","message","split","indexOf","imolOpenTrialPopup","findChild","attr","data-id","type","name","value","elements","findChildren","attribute","j","length","remove","innerHTML","getSelectedCount","style","focus","event","keyCode","sendEvent","deleteLastItem","selectFirstSearchItem","isOpenDialog","OpenLinesConfigEdit","popupTooltip","addEventForTooltip","arNodes","findChildrenByClassName","i","getAttribute","setAttribute","text","showTooltip","hideTooltip","close","PopupWindow","lightShadow","autoHide","darkMode","offsetLeft","offsetTop","bindOptions","position","zIndex","events","onPopupClose","destroy","content","attrs","setAngle","offset","show","ready","ImConnectorConnectorSettings","params","ajaxUrl","createLine","detailPageUrlTemplate","isActiveControlLocked","sendActionRequest","data","location","href","replace","config_id","error","limited","B24","licenseInfoPopup","showErrorPopup","action","callbackSuccess","callbackFailure","ajax","url","method","sessid","bitrix_sessid","timeout","dataType","processData","onsuccess","apply","onfailure","popup","PopupWindowManager","closeByEsc","overlay","backgroundColor","opacity","setButtons","PopupWindowButton","click","popupWindow","setContent"],"mappings":"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"}