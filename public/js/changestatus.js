export class ChangeStatus {
    constructor(array, endpoint, drvrToken, bannerMsg, homeTableStatusValue) {
        this.array = array;
        this.endpoint = endpoint;
        this.drvrToken = drvrToken;
        this.bannerMsg = bannerMsg;
        this.homeTableStatusValue = homeTableStatusValue;
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

        this.drvrStatus = newStatus;
        this.timeStamp = newTime.toISOString().slice(0, 19).replace('T', ' ');

        localStorage.setItem('status', this.drvrStatus);
        this.bannerMsg.textContent = this.drvrStatus;
        this.homeTableStatusValue.textContent = this.drvrStatus;
        console.log(`Driver status currently: ${this.drvrStatus} \n Switched at: ${this.timeStamp} \n Location point: ${this.endpoint} \n Driver access: ${this.drvrToken}`);
        this.updateDBStatus();        
    };

    updateDBStatus() {
        fetch("http://prodriver.local/setstatus", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': this.drvrToken
            },
            body: JSON.stringify({
                drvrStatus: this.drvrStatus,
                drvrStamp: this.timeStamp
            })
        })
            .then(res => {
                return res.json()
            })
            .then(data => console.log(data))
            .catch (error => console.log('Error', error))
    }
}