// combine the members of a convo into a string, or just return the title lol
export function formatTitle(title, members) {
  let displayTitle = '';
  if (title) {
    displayTitle = title;
  } else {
    let i = 0;
    for (const member of members) {
      displayTitle += member.user_name;
      i++;
      if (i !== members.length) {
        displayTitle += ', ';
      }
    }
  }
  return displayTitle;
}

// format a timestamp
export function timeSince(timestamp, format = 'single', ago = false) {
  let string = '';
  const absolute = format === 'single' || format === 'short' ? true : false;
  if (timestamp === 0) {
    return 'Never';
  }
  const lastTimestamp = new Date(timestamp * 1000);
  const currentDate = new Date();
  const timeType = {
    year: {
      text: 'year',
      single: 'y',
      value: null
    },
    month: {
      text: 'month',
      single: 'm',
      value: null
    },
    week: {
      text: 'week',
      single: 'w',
      value: null
    },
    day: {
      text: 'day',
      single: 'd',
      value: null
    },
    hour: {
      text: 'hour',
      single: 'h',
      value: null
    },
    minute: {
      text: 'minute',
      single: 'm',
      value: null
    },
    second: {
      text: 'second',
      single: 's',
      value: null
    }
  };

  // raw milliseconds
  let milli = Math.abs(lastTimestamp - currentDate);
  // years (365 days)
  timeType.year.value = Math.floor(milli / (1000 * 60 * 60 * 24 * 365));
  milli = Math.floor(milli % (1000 * 60 * 60 * 24 * 365));

  // months (30 days)
  timeType.month.value = Math.floor(milli / (1000 * 60 * 60 * 24 * 30));
  milli = Math.floor(milli % (1000 * 60 * 60 * 24 * 30));

  // weeks (7 days)
  timeType.week.value = Math.floor(milli / (1000 * 60 * 60 * 24 * 7));
  milli = Math.floor(milli % (1000 * 60 * 60 * 24 * 7));

  // days (24 hours)
  timeType.day.value = Math.floor(milli / (1000 * 60 * 60 * 24));
  milli = Math.floor(milli % (1000 * 60 * 60 * 24));

  // hours (60 minutes)
  timeType.hour.value = Math.floor(milli / (1000 * 60 * 60));
  milli = Math.floor(milli % (1000 * 60 * 60));

  // minutes (60 seconds)
  timeType.minute.value = Math.floor(milli / (1000 * 60));
  milli = Math.floor(milli % (1000 * 60));

  // seconds (1000 milliseconds)
  timeType.second.value = Math.floor(milli / 1000);
  milli = Math.floor(milli % 1000);

  // loop through each year, month, etc.
  for (const timeKey in timeType) {
    // if the type is not 0, add to the string
    if (timeType[timeKey].value) {
      // if less than 1 minute, for short and single formats
      if (timeType[timeKey].text === 'second' && absolute) {
        return 'Just now';
      }
      let name;
      switch (format) {
        case 'single':
          name = timeType[timeKey].single;
          break;
        case 'short':
          name = ' ' + timeType[timeKey].text;
          break;
      }
      string += timeType[timeKey].value + name;

      // add an s if more than 1
      if (timeType[timeKey].value > 1 && format !== 'single') {
        string += 's';
      }
      // if the format is asking for single or short
      if (absolute) {
        break;
      }
    }
  }
  if (string.length === 0) {
    return 'Just now';
  }
  if (ago) {
    string += ' ago';
  }
  return string;
}