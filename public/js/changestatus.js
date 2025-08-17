import { fetchDrvr } from './drvrapi.js';
export const statusBtns = document.querySelectorAll('.set-status');
export class changeStatus {
    constructor(array, endpoint, drvrToken) {
        this.array = array;
        this.endpoint = endpoint;
        this.drvrToken = drvrToken;
        this.drvrStatus = drvrStatus;
        this.timeStamp = timeStamp;
    }

    static updateDrvrStatusControl() {
        const timeOptions = {
            year: 'numeric',
            month: 'numeric',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit',
            hour12: false 
        }
        this.array.forEach(button => {
            button.addEventListener('click', (e) => {
                if (e.target.classList.contains('.status-enroute-garage')) {
                    localStorage.setItem('status', 'Enroute to garage');
                    let changeStatus = localStorage.getItem('status');
                    bannerMsg.textContent = changeStatus;
                    this.drvrStatus = changeStatus;
                    const newTime = new Date();
                    this.timeStamp = newTime.toLocaleString('en-us', timeOptions);
                }

                if (e.target.classList.contains('.status-checkedin-garage')) {
                    localStorage.setItem('status', 'Arrived at garage');
                    let changeStatus = localStorage.getItem('status');
                    bannerMsg.textContent = changeStatus;
                    this.drvrStatus = changeStatus;
                    const newTime = new Date();
                    this.timeStamp = newTime.toLocaleString('en-us', timeOptions);
                }

                if (e.target.classList.contains('.status-enroute-location')) {
                    localStorage.setItem('status', 'Enroute to location');
                    let changeStatus = localStorage.getItem('status');
                    bannerMsg.textContent = changeStatus;
                    this.drvrStatus = changeStatus;
                    const newTime = new Date();
                    this.timeStamp = newTime.toLocaleString('en-us', timeOptions);
                }

                if (e.target.classList.contains('.status-onlocation')) {
                    localStorage.setItem('status', 'Arrived at location');
                    let changeStatus = localStorage.getItem('status');
                    bannerMsg.textContent = changeStatus;
                    this.drvrStatus = changeStatus;
                    const newTime = new Date();
                    this.timeStamp = newTime.toLocaleString('en-us', timeOptions);
                }

                if (e.target.classList.contains('.status-working-assignment')) {
                    localStorage.setItem('status', 'On assignment');
                    let changeStatus = localStorage.getItem('status');
                    bannerMsg.textContent = changeStatus;
                    this.drvrStatus = changeStatus;
                    const newTime = new Date();
                    this.timeStamp = newTime.toLocaleString('en-us', timeOptions);
                }

                if (e.target.classList.contains('.status-emergency')) {
                    localStorage.setItem('status', 'Emergency');
                    let changeStatus = localStorage.getItem('status');
                    bannerMsg.textContent = changeStatus;
                    this.drvrStatus = changeStatus;
                    const newTime = new Date();
                    this.timeStamp = newTime.toLocaleString('en-us', timeOptions);
                }
            })
        });
    };

    static updateStatusDB() {
        const sendStatus = fetchDrvr;
        sendStatus(endpoint, {
            method: 'POST',
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-Token": this.drvrToken
            },
            body: JSON.stringify(this.drvrStatus, this.timeStamp),
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