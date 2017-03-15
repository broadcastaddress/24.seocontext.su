{"version":3,"file":"invite-dialog.min.js","sources":["invite-dialog.js"],"names":["BX","InviteDialog","bInit","popup","arParams","lastTab","lastUserTypeSuffix","sonetGroupSelector","popupHint","Init","nodeCopyToClipboardButton","document","querySelector","bind","clipboard","copy","value","selectCallback","item","type","search","findChild","attr","data-id","id","appendChild","create","attrs","props","className","window","util","in_array","entityId","children","name","html","events","click","e","SocNetLogDestination","deleteItem","PreventDefault","mouseover","addClass","this","parentNode","mouseout","removeClass","setLinkName","unSelectCallback","elements","findChildren","attribute","j","length","remove","openDialogCallback","PopupWindow","setOptions","popupZindex","style","focus","closeDialogCallback","isOpenSearch","disableBackspace","searchBefore","event","searchBeforeHandler","formName","inputId","keyCode","createSonetGroupTimeout","clearTimeout","searchHandler","linkId","sendAjax","backspaceDisable","unbind","setTimeout","getSelectedCount","innerHTML","message","showMessage","strMessageText","strWarningText","display","margin-top","B24","Bitrix24InviteDialog","ShowForm","cleanNode","showError","strErrorText","bindInviteDialogStructureLink","oBlock","inviteDialogDepartmentPopup","offsetTop","autoHide","angle","position","offset","content","zIndex","buttons","popupContainer","setBindElement","show","PopupMenu","destroy","popupWindow","close","popupSearchWindow","createSocNetGroupWindow","bindInviteDialogSonetGroupLink","sonetGroupBlock","dialogName","getAttribute","obElementBindMainPopup","node","obElementBindSearchPopup","openDialog","onInviteDialogUserTypeSelect","userType","obAllowAddSocNetGroup","setAttribute","bindInviteDialogUserTypeLink","bExtranetInstalled","arItems","text","onclick","push","offsetLeft","lightShadow","onPopupShow","ob","bindInviteDialogChangeTab","action","i","arTabs","arTabsContent","windowObj","top","setTitleBar","getEmail1","res","getEmail2","email","options","selectedIndex","serviceID","parseInt","arConnectMailServicesDomains","setEmail2","strEmail1","strEmail2","disabled","checked","setEmail1","bindSendPasswordEmail","bindInviteDialogSubmit","obRequestData","arSonetGroupsInput","arProcessResult","allow_register","forms","SELF_DIALOG_FORM","allow_register_confirm","allow_register_secret","allow_register_whitelist","allow_register_text","sessid","bitrix_sessid","INVITE_DIALOG_FORM","EMAIL","MESSAGE_TEXT","DEPARTMENT_ID","processSonetGroupsInput","arCode","SONET_GROUPS_CODE","arName","Object","keys","SONET_GROUPS_NAME","ADD_DIALOG_FORM","ADD_EMAIL","ADD_NAME","ADD_LAST_NAME","ADD_POSITION","ADD_SEND_PASSWORD","ADD_MAILBOX_ACTION","ADD_MAILBOX_PASSWORD","ADD_MAILBOX_PASSWORD_CONFIRM","ADD_MAILBOX_DOMAIN","ADD_MAILBOX_USER","ADD_MAILBOX_SERVICE","disableSubmitButton","ajax","url","method","dataType","data","onsuccess","obResponsedata","onfailure","bindInviteDialogClose","onInviteDialogClose","bCloseDialog","onMailboxAction","oldAction","onMailboxRollup","onMailboxServiceSelect","obSelect","domain","arMailServicesUsers","data-service-id","bDisable","oButton","cursor","oForm","arResult","groupCode","len","tagName","k","len2","initHint","nodeId","proxy","proxy_context","showHint","hideHint","darkMode","bindOptions","onPopupClose","setAngle"],"mappings":"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"}