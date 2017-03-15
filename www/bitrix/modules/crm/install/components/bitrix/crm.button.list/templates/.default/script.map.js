{"version":3,"file":"script.min.js","sources":["script.js"],"names":["CrmWebFormList","params","this","init","context","BX","canEdit","nodeHead","querySelector","headHideClass","formAttribute","formAttributeIsSystem","forms","mess","viewList","actionList","formNodeList","querySelectorAll","i","length","formNode","item","formId","getAttribute","isSystem","initForm","caller","id","node","viewUserOptionName","detailPageUrlTemplate","actionRequestUrl","hideDescBtnNode","bind","addClass","userOptions","delay","save","onBeforeDeleteForm","form","list","filter","onAfterDeleteForm","index","util","array_search","onRevertDeleteForm","removeClass","CrmWebFormListItem","push","nodeDelete","nodeCopyToClipboard","nodeCopyToClipboardButton","nodeSettings","nodeViewSettings","nodeView","nodeBtnGetScript","isActiveControlLocked","popupSettings","popupViewSettings","activeController","CrmWebFormListItemActiveDateController","bindControls","prototype","showViewSettings","items","currentViewType","getCurrentViewType","code","hasOwnProperty","view","text","className","onclick","proxy","onClickViewSettingsItem","createPopup","forEach","menuItem","getMenuItem","layout","popupWindow","show","showSettings","popupItem","link","url","redirectToDetailPage","copy","resetCounters","offsetLeft","offsetTop","event","closePopup","changeViewType","firstViewId","viewId","hasClass","viewInfoNode","isAdd","changeClass","showErrorPopup","data","errorAction","popup","PopupWindowManager","create","autoHide","lightShadow","closeByEsc","overlay","backgroundColor","opacity","setButtons","PopupWindowButton","dlgBtnClose","events","click","close","setContent","showConfirmPopup","confirmAction","dlgBtnApply","action","apply","dlgBtnCancel","changeActive","doNotSend","needDeactivate","isActive","deactivate","activate","sendActionRequest","error","revert","limited","B24","licenseInfoPopup","dlgActiveCountLimitedTitle","dlgActiveCountLimitedText","window","location","replace","reload","copiedId","delete","deleteConfirmation","deleteClassName","callbackSuccess","callbackFailure","ajax","method","button_id","sessid","bitrix_sessid","timeout","dataType","processData","onsuccess","onfailure","showScriptPopup","createScriptPopup","scriptPopup","crmCopyScriptContainer","innerHTML","html","popupContentNode","titleBar","dlgGetScriptTitle","content","contentColor","closeIcon","buttons","clipboard","isCopySupported","copyToClipBoardBtn","dlgBtnCopyToClipboard","bindCopyClick","buttonNode","nodeActiveControl","nodeButton","styleDisplay","isShow","displayValue","style","display","popupId","button","PopupMenu","angle","position","offset","onPopupClose","delegate","nodeDate","classDateNow","classDateNowState","classOn","classOff","classBtnOn","classBtnOff","classViewInactive","isNowShowedCounter","isRevert","toggle","actualizeButton","actualizeDate","innerText","isNow"],"mappings":"AAAA,GAAIA,gBAAiB,SAASC,GAE7BC,KAAKC,KAAO,SAASF,GAEpBC,KAAKE,QAAUC,GAAGJ,EAAOG,QACzBF,MAAKI,QAAUL,EAAOK,OACtBJ,MAAKK,SAAWL,KAAKE,QAAQI,cAAc,+BAC3CN,MAAKO,cAAgB,yBACrBP,MAAKQ,cAAgB,0BACrBR,MAAKS,sBAAwB,oCAC7BT,MAAKU,QAELV,MAAKW,KAAOZ,EAAOY,QACnBX,MAAKY,SAAWb,EAAOa,YACvBZ,MAAKa,WAAad,EAAOc,cACzB,IAAIC,GAAed,KAAKE,QAAQa,iBAAiB,IAAMf,KAAKQ,cAAgB,IAC5E,KAAI,GAAIQ,GAAI,EAAGA,EAAIF,EAAaG,OAAQD,IACxC,CACC,GAAIE,GAAWJ,EAAaK,KAAKH,EACjC,IAAII,GAASF,EAASG,aAAarB,KAAKQ,cACxC,IAAIc,GAAWJ,EAASG,aAAarB,KAAKS,wBAA0B,GACpET,MAAKuB,UACJC,OAAUxB,KACVyB,GAAML,EACNM,KAAQR,EACRI,SAAYA,EACZK,mBAAsB5B,EAAO4B,mBAC7BC,sBAAyB7B,EAAO6B,sBAChCC,iBAAoB9B,EAAO8B,mBAI7B,GAAIC,GAAkB3B,GAAG,yBACzB,IAAI2B,EACJ,CACC3B,GAAG4B,KAAKD,EAAiB,QAAS,WACjC3B,GAAG6B,SAAS7B,GAAG,sBAAuB,iCACtCA,IAAG8B,YAAYC,MAAQ,CACvB/B,IAAG8B,YAAYE,KAAK,MAAOpC,EAAO4B,mBAAoB,YAAa,QAKtE3B,MAAKoC,mBAAqB,SAASC,GAElC,GAAIC,GAAOtC,KAAKU,MAAM6B,OAAO,SAASpB,GACrC,MAAOA,GAAKG,UAAY,OAEzB,IAAGgB,EAAKrB,OAAS,EACjB,CACC,OAGDd,GAAG6B,SAAShC,KAAKK,SAAUL,KAAKO,eAGjCP,MAAKwC,kBAAoB,SAASH,GAEjC,GAAII,GAAQtC,GAAGuC,KAAKC,aAAaN,EAAMrC,KAAKU,MAC5C,IAAG+B,GAAS,EACZ,OACQzC,MAAKU,MAAM+B,IAIpBzC,MAAK4C,mBAAqB,SAASP,GAElClC,GAAG0C,YAAY7C,KAAKK,SAAUL,KAAKO,eAGpCP,MAAKuB,SAAW,SAASxB,GAExB,GAAIsC,GAAO,GAAIS,oBAAmB/C,EAClCC,MAAKU,MAAMqC,KAAKV,GAGjBrC,MAAKC,KAAKF,GAGX,SAAS+C,oBAAmB/C,GAE3BC,KAAKwB,OAASzB,EAAOyB,MACrBxB,MAAKyB,GAAK1B,EAAO0B,EACjBzB,MAAK0B,KAAO3B,EAAO2B,IACnB1B,MAAKsB,SAAWvB,EAAOuB,QACvBtB,MAAK6B,iBAAmB9B,EAAO8B,gBAC/B7B,MAAK2B,mBAAqB5B,EAAO4B,kBACjC3B,MAAK4B,sBAAwB7B,EAAO6B,qBAEpC5B,MAAKgD,WAAahD,KAAK0B,KAAKpB,cAAc,yBAC1CN,MAAKiD,oBAAsBjD,KAAK0B,KAAKpB,cAAc,0BACnDN,MAAKkD,0BAA4BlD,KAAK0B,KAAKpB,cAAc,4BAGzDN,MAAKgD,WAAahD,KAAK0B,KAAKpB,cAAc,oCAC1CN,MAAKmD,aAAenD,KAAK0B,KAAKpB,cAAc,sCAC5CN,MAAKoD,iBAAmBpD,KAAK0B,KAAKpB,cAAc,2CAChDN,MAAKqD,SAAWrD,KAAK0B,KAAKpB,cAAc,kCACxCN,MAAKsD,iBAAmBtD,KAAK0B,KAAKpB,cAAc,2CAChDN,MAAKuD,sBAAwB,KAE7BvD,MAAKwD,cAAgB,IACrBxD,MAAKyD,kBAAoB,IAEzBzD,MAAK0D,iBAAmB,GAAIC,yCAAwCnC,OAAQxB,MAC5EA,MAAK4D,aAAa7D,GAEnB+C,mBAAmBe,WAElBC,iBAAkB,WAEjB,GAAIC,KACJ,IAAIC,GAAkBhE,KAAKiE,oBAC3B,KAAI,GAAIC,KAAQlE,MAAKwB,OAAOZ,SAC5B,CACC,IAAKZ,KAAKwB,OAAOZ,SAASuD,eAAeD,GAAO,QAChD,IAAIE,GAAOpE,KAAKwB,OAAOZ,SAASsD,EAChCH,GAAMhB,MACLtB,GAAIyC,EACJG,KAAMD,EAAK,QACXE,UACCN,GAAmBE,EAElB,uCAEA,0CAEFK,QAASpE,GAAGqE,MAAMxE,KAAKyE,wBAAyBzE,QAIlD,IAAIA,KAAKyD,kBACT,CACCzD,KAAKyD,kBAAoBzD,KAAK0E,YAC7B,kCAAoC1E,KAAKyB,GACzCzB,KAAKoD,iBACLW,OAIF,CACCA,EAAMY,QAAQ,SAASxD,GACtB,GAAIyD,GAAW5E,KAAKyD,kBAAkBoB,YAAY1D,EAAKM,GACvDmD,GAASN,UAAYnD,EAAKmD,SAC1BnE,IAAG0C,YAAY+B,EAASE,OAAO3D,KAAM,uCACrChB,IAAG6B,SAAS4C,EAASE,OAAO3D,KAAMyD,EAASN,YACzCtE,MAGJA,KAAKyD,kBAAkBsB,YAAYC,QAEpCC,aAAc,WAEb,IAAIjF,KAAKwD,cACT,CACC,GAAIO,KACJ,IAAIlD,GAAab,KAAKwB,OAAOX,WAAWb,KAAKsB,SAAW,SAAW,OACnE,KAAI,GAAI4C,KAAQrD,GAChB,CACC,IAAKA,EAAWsD,eAAeD,GAAO,QACtC,IAAI/C,GAAON,EAAWqD,EACtB,IAAIgB,IACHzD,GAAIN,EAAKM,GACT4C,KAAMlD,EAAKkD,KACXc,KAAMhE,EAAKiE,IAEZ,QAAOF,EAAUzD,IAEhB,IAAK,OACL,IAAK,OACJyD,EAAUX,QAAUpE,GAAGqE,MAAM,WAAWxE,KAAKqF,qBAAqBrF,KAAKyB,KAAOzB,KAC9E,MACD,KAAK,OACJkF,EAAUX,QAAUpE,GAAGqE,MAAMxE,KAAKsF,KAAMtF,KACxC,MACD,KAAK,iBACJkF,EAAUX,QAAUpE,GAAGqE,MAAMxE,KAAKuF,cAAevF,KACjD,OAEF+D,EAAMhB,KAAKmC,GAGZlF,KAAKwD,cAAgBxD,KAAK0E,YACzB,6BAA+B1E,KAAKyB,GACpCzB,KAAKmD,aACLY,GACCyB,YAAa,GAAIC,UAAW,KAI/BzF,KAAKwD,cAAcuB,YAAYC,QAEhCP,wBAAyB,SAAUiB,EAAOvE,GAEzC,GAAIiD,GAAOpE,KAAKwB,OAAOZ,SAASO,EAAKM,GACrC2C,GAAK3C,GAAKN,EAAKM,EACfzB,MAAK2F,WAAW3F,KAAKyD,kBACrBzD,MAAK4F,eAAexB,IAErBH,mBAAoB,WAEnB,GAAI4B,GAAc,IAClB,KAAI,GAAIC,KAAU9F,MAAKwB,OAAOZ,SAC9B,CACC,IAAKZ,KAAKwB,OAAOZ,SAASuD,eAAe2B,GAAS,QAClD,KAAID,EAAaA,EAAcC,CAE/B,IAAIxB,GAAYtE,KAAKwB,OAAOZ,SAASkF,GAAQ,aAC7C,IAAG3F,GAAG4F,SAAS/F,KAAKqD,SAAUiB,GAC9B,CACC,MAAOwB,IAIT,MAAOD,IAERD,eAAgB,SAAUxB,GAEzB,IAAI,GAAI0B,KAAU9F,MAAKwB,OAAOZ,SAC9B,CACC,IAAKZ,KAAKwB,OAAOZ,SAASuD,eAAe2B,GAAS,QAElD,IAAIxB,GAAYtE,KAAKwB,OAAOZ,SAASkF,GAAQ,aAC7C,IAAIE,GAAehG,KAAKqD,SAAS/C,cAAc,mCAAqCwF,EAAS,KAE7F,IAAIG,GAAQ7B,EAAK3C,IAAMqE,CACvB9F,MAAKkG,YAAYlG,KAAKqD,SAAUiB,EAAW2B,EAC3CjG,MAAKkG,YAAYF,EAAc,gDAAiDC,GAGjF9F,GAAG8B,YAAYE,KAAK,MAAOnC,KAAK2B,mBAAoB3B,KAAKyB,GAAI2C,EAAK3C,KAEnE0E,eAAgB,SAAUC,GAEzBA,EAAOA,KACP,IAAI/B,GAAO+B,EAAK/B,MAAQrE,KAAKwB,OAAOb,KAAK0F,WACzC,IAAIC,GAAQnG,GAAGoG,mBAAmBC,OACjC,yBACA,MAECC,SAAU,KACVC,YAAa,KACbC,WAAY,KACZC,SAAUC,gBAAiB,QAASC,QAAS,MAG/CR,GAAMS,YACL,GAAI5G,IAAG6G,mBACN3C,KAAMrE,KAAKwB,OAAOb,KAAKsG,YACvBC,QAASC,MAAO,WAAWnH,KAAK+E,YAAYqC,aAG9Cd,GAAMe,WAAW,sDAAwDhD,EAAO,UAChFiC,GAAMtB,QAEPsC,iBAAkB,SAAUlB,GAE3BA,EAAOA,KACP,IAAI/B,GAAO+B,EAAK/B,MAAQrE,KAAKwB,OAAOb,KAAK4G,aACzC,IAAIjB,GAAQnG,GAAGoG,mBAAmBC,OACjC,2BACA,MAECC,SAAU,KACVC,YAAa,KACbC,WAAY,KACZC,SAAUC,gBAAiB,QAASC,QAAS,MAG/CR,GAAMS,YACL,GAAI5G,IAAG6G,mBACN3C,KAAMrE,KAAKwB,OAAOb,KAAK6G,YACvBlD,UAAW,6BACX4C,QAASC,MAAO,WAAWnH,KAAK+E,YAAYqC,OAAShB,GAAKqB,OAAOC,MAAM1H,aAExE,GAAIG,IAAG6G,mBACN3C,KAAMrE,KAAKwB,OAAOb,KAAKgH,aACvBT,QAASC,MAAO,WAAWnH,KAAK+E,YAAYqC,aAG9Cd,GAAMe,WAAW,wDAA0DhD,EAAO,UAClFiC,GAAMtB,QAEP4C,aAAc,SAAUlC,EAAOmC,GAE9B,IAAI7H,KAAKwB,OAAOpB,QAChB,CACC,OAGDyH,EAAYA,GAAa,KACzB,IAAG7H,KAAKuD,sBACR,CACC,OAGD,GAAIuE,GAAiB9H,KAAK0D,iBAAiBqE,UAC3C,IAAGD,EACH,CACC9H,KAAK0D,iBAAiBsE,iBAGvB,CACChI,KAAK0D,iBAAiBuE,WAGvB,GAAGJ,EACH,CACC,OAGD7H,KAAKuD,sBAAwB,IAC7BvD,MAAKkI,kBACHJ,EAAiB,aAAe,WACjC,SAAS1B,GAERpG,KAAKuD,sBAAwB,OAE9B,SAAS6C,GAERA,EAAOA,IAAS+B,MAAS,KAAM9D,KAAQ,GACvCrE,MAAKuD,sBAAwB,KAC7BvD,MAAK0D,iBAAiB0E,QAEtB,IAAGhC,EAAKiC,QACR,CACC,IAAIC,MAAQA,IAAI,oBAChB,CACC,OAGDA,IAAIC,iBAAiBvD,KACpB,yBACAhF,KAAKwB,OAAOb,KAAK6H,2BACjB,SAAWxI,KAAKwB,OAAOb,KAAK8H,0BAA4B,eAI1D,CACCzI,KAAKmG,eAAeC,OAMxBf,qBAAsB,SAAUjE,GAE/BsH,OAAOC,SAAW3I,KAAK4B,sBAAsBgH,QAAQ,OAAQxH,GAAQwH,QAAQ,YAAaxH,IAG3FmE,cAAe,WAEdvF,KAAKkI,kBAAkB,iBAAkB,WACxCQ,OAAOC,SAASE,YAGlBvD,KAAM,WAELtF,KAAKkI,kBAAkB,OAAQ,SAAS9B,GACvCpG,KAAKqF,qBAAqBe,EAAK0C,aAGjCC,SAAQ,WAEP/I,KAAKsH,kBACJjD,KAAMrE,KAAKwB,OAAOb,KAAKqI,mBACvBvB,OAAQtH,GAAGqE,MAAM,WAEhB,GAAIyE,GAAkB,uBACtB9I,IAAG6B,SAAShC,KAAK0B,KAAMuH,EACvBjJ,MAAKwB,OAAOY,mBAAmBpC,KAE/BA,MAAKkI,kBACJ,SACA,SAAS9B,GACRpG,KAAKwB,OAAOgB,kBAAkBxC,OAE/B,SAASoG,GACRjG,GAAG0C,YAAY7C,KAAK0B,KAAMuH,EAC1BjJ,MAAKwB,OAAOoB,mBAAmB5C,KAC/BA,MAAKmG,eAAeC,MAIpBpG,SAGLkI,kBAAmB,SAAUT,EAAQyB,EAAiBC,GAErDD,EAAkBA,GAAmB,IACrCC,GAAkBA,GAAmBhJ,GAAGqE,MAAMxE,KAAKmG,eAAgBnG,KAEnEG,IAAGiJ,MACFhE,IAAKpF,KAAK6B,iBACVwH,OAAQ,OACRjD,MACCqB,OAAUA,EACV6B,UAAatJ,KAAKyB,GAClB8H,OAAUpJ,GAAGqJ,iBAEdC,QAAS,GACTC,SAAU,OACVC,YAAa,KACbC,UAAWzJ,GAAGqE,MAAM,SAAS4B,GAC5BA,EAAOA,KACP,IAAGA,EAAK+B,MACR,CACCgB,EAAgBzB,MAAM1H,MAAOoG,QAEzB,IAAG8C,EACR,CACCA,EAAgBxB,MAAM1H,MAAOoG,MAE5BpG,MACH6J,UAAW1J,GAAGqE,MAAM,WACnB,GAAI4B,IAAQ+B,MAAS,KAAM9D,KAAQ,GAClC8E,GAAgBzB,MAAM1H,MAAOoG,KAC5BpG,SAGL8J,gBAAiB,WAEhB3J,GAAG6B,SAAShC,KAAKsD,iBAAkB,4BACnCtD,MAAKkI,kBAAkB,cAAe,SAAS9B,GAC7C,GAAIE,GAAQtG,KAAK+J,mBACjB/J,MAAKgK,YAAYC,uBAAuBC,UAAY9D,EAAK+D,IACzDhK,IAAG0C,YAAY7C,KAAKsD,iBAAkB,4BACtCgD,GAAMtB,QAEP,SAAUoB,GACTjG,GAAG0C,YAAY7C,KAAKsD,iBAAkB,4BACtCtD,MAAKmG,eAAeC,MAGvB2D,kBAAmB,SAAU3D,GAE5B,GAAIpG,KAAKgK,YACT,CACC,MAAOhK,MAAKgK,YAGb5D,EAAOA,KACP,IAAIgE,GAAmBjK,GAAG,mBAC1BH,MAAKgK,YAAc7J,GAAGoG,mBAAmBC,OACxC,gCACA,MAEC6D,SAAUrK,KAAKwB,OAAOb,KAAK2J,kBAC3BC,QAASH,EACTI,aAAc,QACdC,UAAW,KACXhE,SAAU,KACVC,YAAa,KACbC,WAAY,KACZC,SAAUC,gBAAiB,QAASC,QAAS,MAI/C9G,MAAKgK,YAAYC,uBAAyBG,EAAiB9J,cAAc,qCACzE,IAAIoK,KACJ,IAAIvK,GAAGwK,UAAUC,kBACjB,CACC,GAAIC,GAAqB,GAAI1K,IAAG6G,mBAC/B3C,KAAMrE,KAAKwB,OAAOb,KAAKmK,sBACvBxG,UAAW,4BACX4C,QAASC,MAAO,eAEjBuD,GAAQ3H,KAAK8H,EACb1K,IAAGwK,UAAUI,cAAcF,EAAmBG,YAAa3G,KAAMrE,KAAKgK,YAAYC,yBAGnFS,EAAQ3H,KAAK,GAAI5C,IAAG6G,mBACnB3C,KAAMrE,KAAKwB,OAAOb,KAAKsG,YACvBC,QAASC,MAAO,WACfnH,KAAK+E,YAAYqC,YAGnBpH,MAAKgK,YAAYjD,WAAW2D,EAE5B,OAAO1K,MAAKgK,aAEbpG,aAAc,WAEbzD,GAAGwK,UAAUI,cAAc/K,KAAKkD,2BAA4BmB,KAAMrE,KAAKiD,qBAEvE9C,IAAG4B,KAAK/B,KAAKgD,WAAY,QAAS7C,GAAGqE,MAAMxE,KAAK+I,OAAQ/I,MACxDG,IAAG4B,KAAK/B,KAAK0D,iBAAiBuH,kBAAmB,QAAS9K,GAAGqE,MAAMxE,KAAK4H,aAAc5H,MACtFG,IAAG4B,KAAK/B,KAAK0D,iBAAiBwH,WAAY,QAAS/K,GAAGqE,MAAMxE,KAAK4H,aAAc5H,MAC/EG,IAAG4B,KAAK/B,KAAKmD,aAAc,QAAShD,GAAGqE,MAAMxE,KAAKiF,aAAcjF,MAChEG,IAAG4B,KAAK/B,KAAKoD,iBAAkB,QAASjD,GAAGqE,MAAMxE,KAAK8D,iBAAkB9D,MACxEG,IAAG4B,KAAK/B,KAAKsD,iBAAkB,QAASnD,GAAGqE,MAAMxE,KAAK8J,gBAAiB9J,QAExEkG,YAAa,SAAUxE,EAAM4C,EAAW2B,GAEvCA,EAAQA,GAAS,KACjB,KAAIvE,EACJ,CACC,OAGD,GAAGuE,EACH,CACC9F,GAAG6B,SAASN,EAAM4C,OAGnB,CACCnE,GAAG0C,YAAYnB,EAAM4C,KAGvB6G,aAAc,SAAUzJ,EAAM0J,EAAQC,GAErCD,EAASA,GAAU,KACnBC,GAAeA,GAAgB,EAC/B,KAAI3J,EACJ,CACC,OAGDA,EAAK4J,MAAMC,QAAUH,EAASC,EAAe,QAE9C3G,YAAa,SAAS8G,EAASC,EAAQ1H,EAAOhE,GAE7CA,EAASA,KACT,OAAOI,IAAGuL,UAAUlF,OACnBgF,EACAC,EACA1H,GAEC0C,SAAU,KACVjB,WAAYzF,EAAOyF,WAAazF,EAAOyF,YAAc,GACrDC,UAAW1F,EAAO0F,UAAY1F,EAAO0F,WAAa,EAClDkG,OAECC,SAAU,MACVC,OAAQ,IAET3E,QAEC4E,aAAe3L,GAAG4L,SAAS/L,KAAK8L,aAAc9L,UAKlD2F,WAAY,SAASW,GAEpB,GAAGA,GAASA,EAAMvB,YAClB,CACCuB,EAAMvB,YAAYqC,UAGpB0E,aAAc,aAMf,SAASnI,wCAAuC5D,GAE/CC,KAAKwB,OAASzB,EAAOyB,MAErBxB,MAAKiL,kBAAoBjL,KAAKwB,OAAOE,KAAKpB,cAAc,oCACxDN,MAAKgM,SAAWhM,KAAKwB,OAAOE,KAAKpB,cAAc,yCAE/CN,MAAKiM,aAAe,yBACpBjM,MAAKkM,kBAAoB,+BACzBlM,MAAKmM,QAAU,yBACfnM,MAAKoM,SAAW,0BAEhBpM,MAAKkL,WAAalL,KAAKwB,OAAOE,KAAKpB,cAAc,wCACjDN,MAAKqM,WAAa,kCAClBrM,MAAKsM,YAAc,6BACnBtM,MAAKuM,kBAAoB,sCAEzBvM,MAAKwM,mBAAqB,CAC1BxM,MAAKyM,SAAW,MAEjB9I,uCAAuCE,WAEtCkE,SAAU,WAET,MAAO5H,IAAG4F,SAAS/F,KAAKkL,WAAYlL,KAAKqM,aAE1CjE,OAAQ,WAEPpI,KAAKyM,SAAW,IAChBzM,MAAK0M,QAEL,IAAG1M,KAAKwM,mBAAqB,EAC7B,CACCxM,KAAKwM,mBAAqB,EAE3BxM,KAAKyM,SAAW,OAEjBC,OAAQ,WAEP,GAAG1M,KAAK+H,WACR,CACC/H,KAAKgI,iBAGN,CACChI,KAAKiI,aAGPA,SAAU,WAET9H,GAAG6B,SAAShC,KAAKiL,kBAAmBjL,KAAKmM,QACzChM,IAAG0C,YAAY7C,KAAKiL,kBAAmBjL,KAAKoM,SAC5CpM,MAAK2M,iBACL3M,MAAK4M,iBAEN5E,WAAY,WAEX7H,GAAG0C,YAAY7C,KAAKiL,kBAAmBjL,KAAKmM,QAC5ChM,IAAG6B,SAAShC,KAAKiL,kBAAmBjL,KAAKoM,SACzCpM,MAAK2M,iBACL3M,MAAK4M,iBAEND,gBAAiB,WAEhB,GAAI5E,GAAW/H,KAAK+H,UACpB/H,MAAKwB,OAAO0E,YAAYlG,KAAKwB,OAAO6B,SAAUrD,KAAKuM,kBAAmBxE,EACtE/H,MAAKwB,OAAO0E,YAAYlG,KAAKkL,WAAYlL,KAAKqM,YAAatE,EAC3D/H,MAAKwB,OAAO0E,YAAYlG,KAAKkL,WAAYlL,KAAKsM,YAAavE,EAE3D/H,MAAKkL,WAAW2B,UAAY9E,EAAW/H,KAAKkL,WAAW7J,aAAa,mBAAqBrB,KAAKkL,WAAW7J,aAAa,qBAEvHuL,cAAe,WAEd5M,KAAKwB,OAAO0E,YAAYlG,KAAKgM,SAAUhM,KAAKkM,mBAAoBlM,KAAK+H,WAErE,IAAI+E,IAAU9M,KAAKyM,UAAYzM,KAAKwM,mBAAqB,CACzDxM,MAAKwB,OAAO0E,YAAYlG,KAAKgM,SAAUhM,KAAKiM,aAAca,EAE1D9M,MAAKwM"}