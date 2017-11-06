var aweek=document.getElementById("pweek").value;
var dt = new Date();
var week = aweek.split(",");
var toweek = week[dt.getDay()].split("|");
for(var el in toweek){
	document.getElementById(toweek[el]).style.display="block";
}