import { fetchDrvr } from "./drvrapi.js";
const primaryA = document.querySelector('#tableA');
const groupB = document.querySelector('#tableB');
const groupC = document.querySelector('#tableC');
const groupD = document.querySelector('#tableD');
const clickCells = document.querySelectorAll('.editable-data');
const confirmBtn = document.querySelector('#confirm-job');
const cancelBtn = document.querySelector('#cancel-job');
const editBtn = document.querySelector('#edit');
const completeBtn = document.querySelector('#submit-order');
const fetchDriver = fetchDrvr;

window.addEventListener('load', () => {
    fetchDriver("https://prodriver.local/getprofile")
    .then(data => {
        const driver = data;
        const primaryDrvrId = primaryA.childNodes[3].childNodes[1].childNodes[3];
        const primaryDrvrName = primaryA.childNodes[3].childNodes[1].childNodes[5];
        if (driver) {
            primaryDrvrId.textContent = driver['driverid'];
            primaryDrvrName.textContent = `${driver['lastName']}, ${driver['firstName']}`;
        }
    })
    .catch(error => {
        console.error('There was a problem with the fetch operation:', error);
    })
})

window.addEventListener('DOMContentLoaded', () => {
    clickCells.forEach(cell => {
        cell.addEventListener('click', () => {
            if (!cell.querySelector('input')) {
                const currentValue = cell.textContent.trim();
                const input = document.createElement('input');
                input.type = 'text';
                input.classList.add('form-control');
                input.value = currentValue;
                cell.textContent = '';
                cell.appendChild(input);

                input.focus();
                input.addEventListener('blur', () => {
                    const newValue = input.value.trim();
                    cell.textContent = newValue || currentValue;
                });

                input.addEventListener('keydown', (e) => {
                    if (e.key === 'Enter') {
                        input.blur();
                    }
                });
            }
        });
    });
});