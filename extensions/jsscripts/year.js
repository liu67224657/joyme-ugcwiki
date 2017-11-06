var tday = new Date();
var day = tday.getFullYear()+'-'+(tday.getMonth()+1)+'-'+tday.getDate();
$("div[data-day='"+day+"']").show();