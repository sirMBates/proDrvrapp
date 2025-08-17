import { fetchDrvr } from './drvrapi.js';
export const statusBtns = document.querySelectorAll('.set-status');
export class ChangeStatus {
    constructor(array, endpoint, drvrToken, bannerMsg) {
        this.array = array;
        this.endpoint = endpoint;
        this.drvrToken = drvrToken;
        this.bannerMsg = bannerMsg;
        this.drvrStatus = '';
        this.timeStamp = '';
    }

    init() {
        this.array.forEach(button => {
            button.addEventListener('click', (e) => this.updateDrvrStatusControl(e));
        });
    }

    updateDrvrStatusControl(e) {
        const statusMap = {
            'status-enroute-garage': 'Enroute to garage',
            'status-checkedin-garage': 'Arrived at garage',
            'status-enroute-location': 'Enroute to location',
            'status-onlocation': 'Arrived at location',
            'status-working-assignment': 'On assignment',
            'status-emergency': 'Emergency'
        };

        const clickedClass = [...e.target.classList].find(cls => statusMap[cls]);
        if (!clickedClass) return;

        const newStatus = statusMap[clickedClass];
        const newTime = new Date();
        const timeOptions = {
            year: 'numeric',
            month: 'numeric',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit',
            hour12: false 
        }

        this.drvrStatus = newStatus;
        this.timeStamp = newTime.toLocaleString('en-us', timeOptions);

        localStorage.setItem('status', this.drvrStatus);
        this.bannerMsg.textContent = this.drvrStatus;

        this.updateDBStatus();        
    };

    updateDBStatus() {
        fetchDrvr(endpoint, {
            method: 'POST',
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-Token": this.drvrToken
            },
            body: JSON.stringify({
                drvrStatus: this.drvrStatus, 
                timeStamp: this.timeStamp
            }),
        })
        .then((res) => {
            if (!res.ok) {
                throw new Error("Network response unsuccessful.");
            }
            return res.json();
        })
        .then((data) => {
            console.log("Success:", data);
        })
        .catch((error) => {
            console.error("Error: ", error);
        });
    }
}