import { bdayCelebrationHandler } from "./celebration.js";

let celebrationOccured = true;
function timeCelebrationHandler() {
        let dateNow = new Date();
        let timeNow = dateNow.toLocaleDateString();
        const drvrbirthday = document.querySelector('#drvrbday');
        if (typeof(drvrbirthday.value) === "string" && drvrbirthday.value === timeNow) {
                let bdaySong = document.createElement("audio");
                let mainContent = document.querySelector('main');
                mainContent.insertAdjacentElement('afterbegin', bdaySong);
                $(bdaySong).attr('src', '../audio/happy-birthday-clip.mp3');
                bdaySong.play();
                const celebration = new JSConfetti();
                bdayCelebrationHandler(celebration);
                bdaySong.addEventListener('ended', () => {
                        bdaySong.remove();
                })
        }
        return setTimeout(() => {
                if (celebrationOccured) {
                        drvrbirthday.value = "";
                        celebrationOccured = false;
                }
        }, 34500)
};


function handleCelebration () {
        let time = new Date();
        let timeHour = time.getHours();
        if (timeHour >= 6 || timeHour <= 23) {
                window.addEventListener('load', timeCelebrationHandler, false);
        }
};
handleCelebration();

window.addEventListener('resize', () => {
        let btnGrp = document.querySelector('#status-btns');
        const screenSize = window.innerWidth;
        if (screenSize >= 900) {
                $(btnGrp).removeClass('btn-group-sm');
                $(btnGrp).addClass('btn-group-lg');
                console.log("Screen size is large enough for large button group.");
        } else if (screenSize < 900) {
                $(btnGrp).removeClass('btn-group-lg');
                $(btnGrp).addClass('btn-group-sm');
                console.log("Screen size is small enough for small button group.");
        }
}, false);

window.addEventListener('load', () => {
        let btnGrp = document.querySelector('#status-btns');
        const screenSize = window.innerWidth;
        if (screenSize >= 900) {
                $(btnGrp).removeClass('btn-group-sm');
                $(btnGrp).addClass('btn-group-lg');
                //console.log("Screen size is large enough for large button group.");
        } else if (screenSize < 900) {
                $(btnGrp).removeClass('btn-group-lg');
                $(btnGrp).addClass('btn-group-sm');
                //console.log("Screen size is small enough for small button group.");
        }
}, false);
        
const updateStatusBtns = document.querySelector('#update-status-con');
localStorage.setItem('status', 'official');
updateStatusBtns.addEventListener('click', (e) => {
        if (e.target === statBtns.childNodes[1]) {
                localStorage.setItem('status', 'Enroute to garage.');
                console.log('this is btn 1.');
        }

        if (e.target === statBtns.childNodes[3]) {
                localStorage.setItem('status', 'Arrived at garage.');
                console.log('this is btn 2.');
        }

        if (e.target === statBtns.childNodes[5]) {
                localStorage.setItem('status', 'Enroute to customer location.');
                console.log('this is btn 3.');
        }

        if (e.target === statBtns.childNodes[7]) {
                localStorage.setItem('status', 'Arrived at location.');
                console.log('this is btn 4.');
        }
}, false);

const qString = window.location.search;
const urlParams = new URLSearchParams(qString);
const paramValue = urlParams.get('status');
const changeStatusValue = document.querySelector('table').childNodes[3].childNodes[1].childNodes[11].childNodes;

if (paramValue === 'official') {
        window.location.href = 'home';
}

window.addEventListener('load', () => {
        changeStatusValue.innerHTML = localStorage.getItem('status');
}) 

