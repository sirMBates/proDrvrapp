export async function fetchDrvr(url, options = {}) {
    const response = await fetch(url, options);
    if (!response.ok) {
        throw new Error('Network response was not ok');
    }
    return await response.json();
}