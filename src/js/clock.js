const timeDateCon = document.querySelector('#clock_container');
const dateCon = timeDateCon.childNodes[1];
const timeCon = timeDateCon.childNodes[3];
const dayOfWeek = dateCon.childNodes[1];
const month = dateCon.childNodes[3];
const dayDate = dateCon.childNodes[5];
const year = dateCon.childNodes[7];
const timeHr = timeCon.childNodes[1];
const timeMins = timeCon.childNodes[5];
//const timeSecs = timeCon.childNodes[7];
const meridiem = timeCon.childNodes[9]; 
//console.log(timeSecs);
const bannerClock = setInterval(() => {
        let dateTime = new Date(); 
        let dt = dateTime;
        let dthrs = dt.getHours();
        let dtmins = dt.getMinutes();
        //let dtsecs = dt.getSeconds();
        let dtweekDay = dt.getDay();
        let dMonth = dt.getMonth();
        let dDay = dt.getDate();
        let dYear = dt.getFullYear();      

        // Check whether Am or Pm
        let timeFormat = dthrs >= 12 ? 'PM' : "AM";

        // Find current hour in Am-Pm format
        dthrs = dthrs % 12;

        // Display '0' as ''12
        dthrs = dthrs ? dthrs : 12;

        if (dthrs < 10) {
                dthrs = '0' + dthrs;
        }
        if (dtmins < 10) {
                dtmins = '0' + dtmins;
        }

        function fullWeekDay(day) {
                switch(day) {
                        case 0:
                                day = "Sun";
                                break;
                        case 1:
                                day = "Mon";
                                break;
                        case 2:
                                day = "Tue";
                                break;
                        case 3:
                                day = "Wed";
                                break;
                        case 4:
                                day = "Thu";
                                break;
                        case 5:
                                day = "Fri";
                                break;
                        case 6:
                                day = "Sat";
                                break;
                }
                return day;
        }

        function monthName(month) {
                switch (month) {
                        case 0:
                                month = "Jan";
                                break;
                        case 1:
                                month = "Feb";
                                break;
                        case 2:
                                month = "Mar";
                                break;
                        case 3:
                                month = "Apr";
                                break;
                        case 4:
                                month = "May";
                                break;
                        case 5:
                                month = "Jun";
                                break;
                        case 6:
                                month = "Jul";
                                break;
                        case 7:
                                month = "Aug";
                                break;
                        case 8:
                                month = "Sep";
                                break;
                        case 9:
                                month = "Oct";
                                break;
                        case 10:
                                month = "Nov";
                                break;
                        case 11:
                                month = "Dec";
                                break;
                }
                return month;
        }

        dayOfWeek.textContent = fullWeekDay(dtweekDay);
        month.textContent = monthName(dMonth);
        dayDate.textContent = dDay + ',';
        year.textContent = dYear;
        timeHr.textContent = dthrs;
        timeMins.textContent = dtmins;
        //timeSecs.textContent = dtsecs;
        meridiem.textContent = timeFormat;

}, 1000);

$(window).on('load', bannerClock, false);
