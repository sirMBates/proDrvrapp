import { proDriverRequest } from '../shared/helpers.js';

const userToken = document.querySelector('#userToken')?.value ?? '';

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
    if (!userToken) {
        throw new Error('Driver security token is unavailable.');
    }

    const response = await proDriverRequest('/updatepreferences', {
        method: 'PATCH',
        headers: {
            'X-CSRF-Token': userToken
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
