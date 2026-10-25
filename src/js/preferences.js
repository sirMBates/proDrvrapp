import { proDriverRequest } from './helpers.js';

const drvrToken = document.querySelector('#drvrToken')?.value ?? '';

export async function getPreferences() {
    const response = await proDriverRequest('/getpreferences', {
        method: 'GET'
    });

    if (response?.status !== 'success') {
        throw new Error(response?.message ?? 'Unable to retrieve preferences.');
    }

    return response.data.preferences;
}

export async function updatePreference(preference, value) {
    if (!drvrToken) {
        throw new Error('Driver security token is unavailable.');
    }

    const response = await proDriverRequest('/updatepreferences', {
        method: 'PATCH',
        headers: {
            'X-CSRF-Token': drvrToken
        },
        body: JSON.stringify({
            preference,
            value
        })
    });

    if (response?.status !== 'success') {
        throw new Error(response?.message ?? 'Unable to update preference.');
    }

    return response.data.preferences;
};
