function selectAllMessages() {
	$('input[type=checkbox]').prop('checked', true);
}

var currentNotification = 0;
var totalNotifications = 0;

function titleBarFlash() {
	setInterval(()=>
	{
		document.title = document.title == "Training Done!" ? "" : "Training Done!";
	}, 2000);
}

function slideNotificationRight() {
	totalNotifications = $('#notificationSlider p.notification').length;
	if(currentNotification >= totalNotifications - 1) {
		return false;
	}

	$('p.notification[data-notification-id=\"' + currentNotification + '\"]').hide({
		'effect': 'slide',
		'direction': 'left',
		'duration': 200,
		'complete': function() {
			currentNotification++;
			$('p.notification[data-notification-id=\"' + currentNotification + '\"]').show({
				'effect': 'slide',
				'direction': 'right',
				'duration': 200,
			});
		}
	});
}
function slideNotificationLeft() {
	if(currentNotification == 0) {
		return false;
	}

	$('p.notification[data-notification-id=\"' + currentNotification + '\"]').hide({
		'effect': 'slide',
		'direction': 'right',
		'duration': 200,
		'complete': function() {
			currentNotification--;
			$('p.notification[data-notification-id=\"' + currentNotification + '\"]').show({
				'effect': 'slide',
				'direction': 'left',
				'duration': 200
			});
		}
	});
}
