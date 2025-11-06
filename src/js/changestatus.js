import { showFlashAlert } from "./helpers.js";
import { handleStatusFetch } from "./pwa.js";
export class ChangeStatus {
    constructor(array, drvrToken, bannerMsg) {
        this.array = array;
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
            'status-end-shift': 'End of Shift',
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
        let viewedTimeStamp = newTime.toLocaleString('en-us', timeOptions);
        //this.timeStamp = newTime.toISOString().slice(0, 19).replace('T', ' ');
        this.timeStamp = newTime.toISOString();

        localStorage.setItem('status', this.drvrStatus);
        this.bannerMsg.textContent = this.drvrStatus;
        console.log(`Driver access: ${this.drvrToken} \n Driver status currently: ${this.drvrStatus} \n Switched at: ${viewedTimeStamp}`);
        this.updateDBStatus(this.drvrToken, this.drvrStatus, this.timeStamp);        
    };

    async updateDBStatus(token, drvrstatus, stamp) {
        try {
            const result = await handleStatusFetch({
                method: 'POST',
                mode: 'cors',
                credentials: 'include',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': token
                },
                body: JSON.stringify({
                    drvrStatus: drvrstatus,
                    drvrStamp: stamp
                })
            });

            if ( result.status === 'success' ) {
                showFlashAlert(result.status, result.message);
            } else if ( result.status === 'queued' ) {
                showFlashAlert('info', result.message || 'Status saved offline - will sync.');
            } else {
                showFlashAlert('error', result.message || 'Unable to update status.');
            }
            /*.then(res => {
                return res.json()
            })*/
            /*.then(data => console.log(data))
            .catch (error => console.log('Error', error))*/
        } catch (err) {
            //console.error('[STATUS] Failed to send status:', err);
            showFlashAlert('error', 'Error saving status.');
        }
    }
};