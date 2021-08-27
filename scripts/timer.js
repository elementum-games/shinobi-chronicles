function countdownTimer(time, element_id) {
	if(element_id.length < 1) {
		return true;
	}
	
	var timeDisplay = "";
	
	
	
	time--;
	if(time > 0) {
		timeDisplay = timeRemaining(time, 'short', true, true) + " remaining";
		$("#" + element_id).text(timeDisplay);
	
		setTimeout("countdownTimer(" + time + ", '" + element_id + "')", 995);
	}
	else {
		$("#" + element_id).text('Finished');
	}
}


function timeRemaining(time_remaining, format, include_days, include_seconds) {
	if(!format) {
		format = 'short';
	}
	include_days = true;
	include_seconds = true;
	
	
	var days = 0;
	var hours = 0;
	var minutes = 0;
	var seconds = 0;
	
	if(include_days) {
		days = Math.floor(time_remaining / 86400);
		time_remaining -= days * 86400;
	}
	
	hours = Math.floor(time_remaining / 3600);
	time_remaining -= hours * 3600;
	
	if(include_seconds) {
		minutes = Math.floor(time_remaining / 60);
		time_remaining -= minutes * 60;
		
		seconds = time_remaining;
	}
	else {
		minutes = Math.ceil(time_remaining / 60);
	}
	
	if(hours < 10) {
		hours = '0' + hours;
	}
	if(minutes < 10) {
		minutes = '0' + minutes;
	}
	if(include_seconds && seconds < 10) {
		seconds = '0' + seconds;
	}
	
	string = '';
	if(format == 'long') {
		if(days && include_days) {
			string = "days day(s), hours hour(s), minutes minute(s)";
		}
		else if(hours && hours != '00') {
			string = "hours hour(s), minutes minute(s)";
		}
		else {
			string = "minutes minute(s)";
		}
		
		if(include_seconds) {
			string = string + ", seconds seconds";
		}
	}
	else if(format == 'short') {
		if(days) {
			string = days + " day(s), " + hours + ":" + minutes;
		}
		else if(hours && hours != '00') {
			string = hours + ":" + minutes;
		}
		else {
			string = minutes;
		}
		
		if(include_seconds) {
			string = string + ":" + seconds;
		}
	}
	return string;
}
