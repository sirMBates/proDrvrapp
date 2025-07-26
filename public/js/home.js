import { bdayCelebrationHandler } from "./celebration.js";
import { bannerMsg } from "./main.js";
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
        let btnGrp = document.querySelector('#update-status-con');
        const screenSize = window.innerWidth;
        if (screenSize <= 900) {
                $(btnGrp).removeClass('btn-group-lg');
                $(btnGrp).addClass('btn-group-sm');
                //console.log("Screen size is small enough for small button group.");
        } else if (screenSize >= 901) {
                $(btnGrp).removeClass('btn-group-sm');
                $(btnGrp).addClass('btn-group-lg');
                //console.log("Screen size is large enough for large button group.");
        }
}, false);

window.addEventListener('load', () => {
        let btnGrp = document.querySelector('#update-status-con');
        const screenSize = window.innerWidth;
        if (screenSize <= 900) {
                $(btnGrp).removeClass('btn-group-lg');
                $(btnGrp).addClass('btn-group-sm');
                //console.log("Screen size is small enough for small button group.");
        } else if (screenSize > 901) {
                $(btnGrp).removeClass('btn-group-sm');
                $(btnGrp).addClass('btn-group-lg');
                //console.log("Screen size is large enough for large button group.");
        }
}, false);

const changeStatusValue = document.querySelector('table').childNodes[3].childNodes[1].childNodes[11];
const updateStatusBtns = document.querySelector('#update-status-con');
window.addEventListener('load', () => {
        if (sessionStorage.getItem('status') === null && localStorage.getItem('status') === null) {
                sessionStorage.setItem('status', 'Official');
                let startUpStatus = sessionStorage.getItem('status');
                changeStatusValue.textContent = startUpStatus;                
        } else if (localStorage.getItem('status') !== null) {
                sessionStorage.clear;
                let drvrStatus = localStorage.getItem('status');
                changeStatusValue.textContent = drvrStatus;
        } else if (sessionStorage.getItem('status') !== null && localStorage.getItem('status') === null) {
                let drvrStatus = sessionStorage.getItem('status');
                changeStatusValue.textContent = drvrStatus;
        }
}, false);
//console.log(changeStatusValue);
updateStatusBtns.addEventListener('click', (e) => {
        if (e.target === updateStatusBtns.childNodes[1]) {
                localStorage.setItem('status', 'Enroute to garage');
                let changeStatus = localStorage.getItem('status');
                changeStatusValue.textContent = changeStatus;
                bannerMsg.textContent = changeStatus;
                //console.log(changeStatusValue);
        }

        if (e.target === updateStatusBtns.childNodes[3]) {
                localStorage.setItem('status', 'Arrived at garage');
                let changeStatus = localStorage.getItem('status');
                changeStatusValue.textContent = changeStatus;
                bannerMsg.textContent = changeStatus;
                //console.log(changeStatusValue);
        }

        if (e.target === updateStatusBtns.childNodes[5]) {
                localStorage.setItem('status', 'Enroute to pickup location');
                let changeStatus = localStorage.getItem('status');
                changeStatusValue.textContent = changeStatus;
                bannerMsg.textContent = changeStatus;
                //console.log(changeStatusValue);
        }

        if (e.target === updateStatusBtns.childNodes[7]) {
                localStorage.setItem('status', 'Arrived at location');
                let changeStatus = localStorage.getItem('status');
                changeStatusValue.textContent = changeStatus;
                bannerMsg.textContent = changeStatus;
                //console.log(changeStatusValue);
        }
}, false);
