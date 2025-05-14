/*const spotTime = document.getElementById('#spotTime');
const dropTime = document.getElementById('#dropTime');
const jobTimeTotal = document.getElementById('#totalHrs');*/

const startTime = new Date(2023, 2, 19, 20, 30).getTime();
const finishTime = new Date(2023, 2, 21, 2, 22).getTime();

function timeDifference (spot, drop) {
    return drop - spot;
};

let totalTime = (time) => {
    let hh = Math.floor(time / 1000 / 60 / 60);
    time -= hh * 1000 * 60 * 60;

    let mm = Math.floor(time / 1000 / 60);
    time -= mm * 1000 * 60;

    /*let ss = Math.floor(time / 1000);
    time -= ss * 1000;*/

    return hh + ':' + mm;
};

function timeToDecimal (time) {
    time = time.split(':');
    return console.log(parseFloat(parseInt(time[0], 10) + parseInt(time[1], 10)/60).toFixed(2));
};

timeToDecimal(totalTime(timeDifference(startTime, finishTime)));