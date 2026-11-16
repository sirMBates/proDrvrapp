
import { proDriverRequest } from '../shared/helpers.js';

const dispatchProfileName = document.querySelector('#dispatch-profile-name');
const dispatchProfileStaffId = document.querySelector('#dispatch-profile-staff-id');
const userToken = document.querySelector('#userToken')?.value ?? '';

async function loadDispatchProfile() {
    if (!dispatchProfileName || !dispatchProfileStaffId) {
        return;
    }

    if (!userToken) {
        console.error('Dispatch CSRF token is unavailable.');
        dispatchProfileName.textContent = 'Profile unavailable';
        return;
    }

    try {
        const profile = await proDriverRequest('/getdispatchprofile', {
            method: 'GET',
            headers: {
                'X-CSRF-Token': userToken
            }
        });

        const fullName = [
            profile.firstName,
            profile.lastName
        ].filter(Boolean).join(' ');

        dispatchProfileName.textContent = fullName || 'Dispatch';
        dispatchProfileStaffId.textContent = profile.staffId || '';

    } catch (error) {
        console.error('Unable to load dispatch profile:', error);
        dispatchProfileName.textContent = 'Profile unavailable';
        dispatchProfileStaffId.textContent = '';
    }
}

document.addEventListener('DOMContentLoaded', () => {
    loadDispatchProfile();
});
