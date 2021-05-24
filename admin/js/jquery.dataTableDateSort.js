function trim(str) {
str = str.replace(/^\s+/, '');
for (var i = str.length - 1; i >= 0; i--) {
if (/\S/.test(str.charAt(i))) {
str = str.substring(0, i + 1);
break;
}
}
return str;
}

function dateHeight(dateStr){
if (trim(dateStr) != '') {
var frDate = trim(dateStr).split(' ');
var frTime = frDate[1].split(':');
var frDateParts = frDate[0].split('/');
var day = frDateParts[0] * 60 * 24;
var month = frDateParts[1] * 60 * 24 * 31;
var year = frDateParts[2] * 60 * 24 * 366;
var hour = frTime[0] * 60;
var minutes = frTime[1];
var x = day+month+year+hour+minutes;
} else {
var x = 99999999999999999; //GoHorse!
}
return x;
}

jQuery.fn.dataTableExt.oSort['date-euro-asc'] = function(a, b) {
var x = dateHeight(a);
var y = dateHeight(b);
var z = ((x < y) ? -1 : ((x > y) ? 1 : 0));
return z;
};

jQuery.fn.dataTableExt.oSort['date-euro-desc'] = function(a, b) {
var x = dateHeight(a);
var y = dateHeight(b);
var z = ((x < y) ? 1 : ((x > y) ? -1 : 0));
return z;
};


