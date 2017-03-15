{"version":3,"file":"logic.min.js","sources":["logic.js"],"names":["BX","namespace","Tasks","Component","TaskDetailPartsReplication","Util","Widget","extend","sys","code","options","data","can","constants","PERIOD_DAILY","PERIOD_WEEKLY","PERIOD_MONTHLY","PERIOD_YEARLY","REPEAT_TILL_ENDLESS","REPEAT_TILL_TIMES","REPEAT_TILL_DATE","MONDAY","TUESDAY","THURSDAY","SUNDAY","methods","construct","this","callConstruct","date","control","type","isElementNode","instances","startDate","DatePicker","scope","displayFormat","defaultTime","H","M","S","endDate","time","timePicker","inputId","value","option","TIME","vars","prevPeriod","getCurrentPeriod","bindEvents","onPeriodChange","handler","delegate","onUpdateHint","bindDelegateControl","passCtx","onSetPeriodValue","bindDelegate","tag","attr","bindInstantChange","bindEvent","hintManager","bindHelp","getValueString","node","util","in_array","setConstraintPanelHeight","period","nodeToShow","height","pos","style","dropCSSFlags","setCSSFlag","htmlspecialchars","nodeToHide","addClass","removeClass","hint","parseTime","params","PERIOD","EVERY_DAY","getValueInt","WORKDAY_ONLY","DAILY_MONTH_INTERVAL","EVERY_WEEK","WEEK_DAYS","getWeekDays","MONTHLY_DAY_NUM","MONTHLY_MONTH_NUM_1","MONTHLY_TYPE","getCheckedControlValues","MONTHLY_WEEK_DAY_NUM","MONTHLY_WEEK_DAY","MONTHLY_MONTH_NUM_2","YEARLY_TYPE","YEARLY_DAY_NUM","YEARLY_MONTH_1","YEARLY_WEEK_DAY_NUM","YEARLY_WEEK_DAY","YEARLY_MONTH_2","START_DATE","getValue","END_DATE","TIMES","REPEAT_TILL","getRepeatTill","innerHTML","makeHintText","tzOffset","parseInt","condition","message","replace","formatTimeAmount","dayNumber","WORK_DAY","timesInMonth","getMessagePlural","number","wd","length","k","push","join","mNumber","wdGender","getWeekDayGender","wdName","getWeekDayName","each","mName","constraint","repeatTimes","short","format","convertBitrixFormat","parseDate","till","endless","num","controlName","toString","val","isNaN","id","result","nodes","controlAll","checked","found","match","h","m","pad","n","repeat","base","openClock","formatDisplay","trim","formatValue","setTime","bind","onTimeChange","cbName","isFunction","window","call","fireEvent","ts","dateStampToString","RegExp","hasAmPm","pm","toLowerCase","stamp","Date"],"mappings":"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"}