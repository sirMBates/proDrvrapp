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
        console.log(`Driver status currently: ${this.drvrStatus} \n Switched at: ${this.timeStamp} \n Location point: ${this.endpoint} \n Driver access: ${this.drvrToken}`);
        this.updateDBStatus(this.drvrToken, this.endpoint, this.drvrStatus, this.timeStamp);        
    };

    async updateDBStatus(token, endpoint, status, stamp) {
        try {
            const response = await fetch(endpoint, {
                mode: 'cors',
                credentials: 'include',
                method: 'POST',
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-Token": token
                },
                body: JSON.stringify({ 
                    drvrStatus: status, 
                    timeStamp: stamp 
                }),
            });

            const contentLength = response.headers.get("content-length");
            const contentType = response.headers.get("content-type");

            let result = null;

            if (response.ok) {
                if (contentLength !== "0" && contentType && contentType.includes("application/json")) {
                    const text = await response.text();
                    result = text ? JSON.parse(text) : null;
                    console.log(`Status updated successfully: ${result?.message || 'No message returned.'}`);
                } else {
                    console.log("Status updated successfully (no response body).");
                }
            } else {
                console.log(`Error: ${result?.error || 'Unknown error occurred.'}`);
            }
        } catch (error){
            console.error("Error: ", error);
            console.log('An unexpected error occurred.');
        }
    }

}