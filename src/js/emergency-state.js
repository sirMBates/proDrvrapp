import { fetchDrvr } from './helpers.js';

let emergencyActive = null;

export async function syncEmergencyState() {
    try {
        const result = await fetchDrvr('/emergencystatus', {
            method: 'GET',
            headers: {
                'Accept': 'application/json'
            }
        });

        emergencyActive = result?.emergency_active === true;

        if (emergencyActive) {
            localStorage.setItem('isActiveEmergency', 'true');
        } else {
            localStorage.removeItem('isActiveEmergency');
        }

        return emergencyActive;

    } catch (error) {
        console.error('[EMERGENCY] Unable to sync Emergency state.', error);

        emergencyActive = null;

        return null;
    }
};

export function getEmergencyState() {
    return emergencyActive;
};

export function isEmergencyActive() {
    return emergencyActive === true;
};