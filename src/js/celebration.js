import { buildModal } from "./appmodal.js";
import JSConfetti from "js-confetti";
export function bdayCelebrationHandler() {
    const drvrName = localStorage.getItem('driverName');
    const jsConfetti = new JSConfetti;
    setTimeout(() => {
        jsConfetti.addConfetti({
            emojis: ['🎈', '🧨', '🎁', '🍾', '🎂'],
            emojiSize: 25,
            confettiRadius: 10,
            confettiNumber: 300
        }).then(() => { 
            jsConfetti.addConfetti({ confettiNumber: 500 })
        }).then(() => { 
            setTimeout(() => { 
                jsConfetti.addConfetti({ confettiNumber: 750 })
            }, 2500)
        }).then(() => { 
            setTimeout(() => { 
                jsConfetti.addConfetti({ confettiNumber: 1000 })
            }, 5000)
        }).then(() => {
            setTimeout(() => {
                const celebrateModal = document.querySelector('#custom-modal');
                const celebrateBtn = document.querySelector('#custom-ok');
                $(celebrateModal).modal('toggle');
                celebrateModal.addEventListener('shown.bs.modal', () => {
                    buildModal.custom('On behalf of ProDriver, wishing you a safe & special happy birthday 🎂 enjoy!', 'bg-btd-celebrate-bkgd-clr', 'fa-cake-candles', 'text-primary', `Hey, ${drvrName}!`, 'btn-primary', 'Thank You');
                })
                celebrateBtn.addEventListener('click', () => {
                    $(celebrateModal).modal('toggle');
                })
            }, 7500)
        })
    }, 1500)
};