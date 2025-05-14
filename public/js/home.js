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