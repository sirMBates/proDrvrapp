import { showFlashAlert } from "./helpers.js";
import { handleStatusFetch } from "./pwa.js";

const STATUS_MAP = Object.freeze({
    'status-enroute-garage': 'Enroute to garage',
    'status-checkedin-garage': 'Arrived at garage',
    'status-onlocation': 'Arrived at location',
    'status-working-assignment': 'On assignment',
    'status-end-shift': 'End of Shift',
    'status-emergency': 'Emergency'
});
export class ChangeStatus {
    constructor(array, drvrToken, bannerMsg) {
        this.array = array;
        this.drvrToken = drvrToken;
        this.bannerMsg = bannerMsg;
        this.drvrStatus = '';
    }

    init() {
        this.array.forEach(button => {
            button.addEventListener('click', (e) => this.updateDrvrStatusControl(e));
        });
    }

    async updateDrvrStatusControl(e) {
        const clickedClass = [...e.currentTarget.classList].find(cls => STATUS_MAP[cls]);
        if (!clickedClass) return;

        const newStatus = STATUS_MAP[clickedClass];

        await this.updateDBStatus(this.drvrToken, newStatus);       
    };

    async updateDBStatus(token, driverStatus) {
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
                    drvrStatus: driverStatus
                })
            });

            if ( result.status === 'success' ) {
                const statusRecord = result.data;
                this.drvrStatus = statusRecord.driverStatus;
                localStorage.setItem('status', this.drvrStatus);
                this.bannerMsg.textContent = this.drvrStatus;
                console.log(`Driver status: ${this.drvrStatus}\n` + `Updated at: ${statusRecord.statusTimestamp}`);
                showFlashAlert(result.status, result.message);
                return;
            } 
            
            if ( result.status === 'queued' ) {
                this.drvrStatus = driverStatus;
                this.bannerMsg.textContent = this.drvrStatus;
                showFlashAlert('info', result.message || 'Status saved offline - will sync.');
                return;                
            }
            
            if ( result.status === 'error' ) {
                showFlashAlert(result.status, result.message);
            }
        } catch (err) {
            showFlashAlert('error', 'Error saving status.');
        }
    }
};