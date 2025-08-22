import { fetchDrvr } from "./drvrapi";
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
        let viewedTimeStamp = newTime.toLocaleString('en-us', timeOptions);
        //this.timeStamp = newTime.toISOString().slice(0, 19).replace('T', ' ');
        this.timeStamp = newTime.toISOString();

        localStorage.setItem('status', this.drvrStatus);
        this.bannerMsg.textContent = this.drvrStatus;
        console.log(`Driver status currently: ${this.drvrStatus} \n Switched at: ${viewedTimeStamp} \n Location point: ${this.endpoint} \n Driver access: ${this.drvrToken}`);
        this.updateDBStatus(this.endpoint, this.drvrToken, this.drvrStatus, this.timeStamp);        
    };

    updateDBStatus(endpoint, token, drvrstatus, stamp) {
        fetchDrvr(endpoint, {
            credentials: 'include',
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': token
            },
            body: JSON.stringify({
                drvrStatus: drvrstatus,
                drvrStamp: stamp
            })
        })
            /*.then(res => {
                return res.json()
            })*/
            .then(data => console.log(data))
            .catch (error => console.log('Error', error))
    }
}