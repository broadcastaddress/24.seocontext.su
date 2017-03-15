{"version":3,"file":"mobile_voximplant.min.js","sources":["mobile_voximplant.js"],"names":["window","BX","MobileVoximplant","isConnected","isInitialized","isReady","plugin","BXCordovaPlugin","listeners","internalListeners","onIncomingCall","data","incomingCall","call","displayName","videoCall","headers","callId","executeCallback","events","IncomingCall","onConnectionEstablished","ConnectionEstablished","onConnectionClosed","ConnectionClosed","onConnectionFailed","ConnectionFailed","onSDKReady","SDKReady","onAuthResult","code","console","error","AuthResult","name","log","getInstance","this","init","exec","getListeners","addEventListener","eventName","handler","connected","connect","callIfReady","disconnect","loginWithOneTimeKey","login","hash","number","useVideo","callParams","MobileVoximplantCall","setOperatorACDStatus","onlineStatus","func","key","context","proxy","MicAccessResult","NetStatsReceived","pluginObject","callState","states","DISCONNECTED","params","phoneNumber","JSON","stringify","onCreateCallback","onCreate","onCallConnected","onConnected","onCallDisconnected","onDisconnected","onCallFailed","onFailed","onProgressToneStart","onProgressToneStop","prototype","result","start","removeEventListener","answer","hangup","decline","id","state","muteMicrophone","mute","unmuteMicrophone","setUseLoudSpeaker","enabled","sendMessage","text","sendTone","digit","Disconnected","CONNECTED","Connected","Failed","CONNECTING","ProgressToneStart","ProgressToneStop","MobileCallUI","onHangup","onSpeakerphoneChanged","onMuteChanged","onPauseChanged","onNumpadClicked","onFormFolded","onFormExpanded","onCloseClicked","onSkipClicked","onAnswerClicked","onNumpadClosed","onNumpadButtonClicked","onPhoneNumberReceived","onContactListChoose","onContactListMenuChoose","customEvents","listener","form","eventListener","onEvent","jsCallbackProvider","setListener","showContactList","showContactListMenu","items","event","list","show","showMenu","preparedItems","numpad","close","animated","startData","updateHeader","labels","avatar","headerLabels","avatarUrl","updateMiddle","buttons","middleLabels","middleButtons","updateFooter","footerLabels","rollUp","expand","startTimer","pauseTimer","resumeTimer","stopTimer","cancelDelayedClosing","setCloseDurationDelay","duration","playSound","soundId","stopSound","sound","INCOMING","START_CALL","FINISHED","STARTED","OUTGOING","WAITING","CALLBACK","contentDesc","textColor","display","sort"],"mappings":"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"}