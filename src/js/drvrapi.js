export async function fetchDrvr(url, options = {}) {
    const headers = {
        "X-Requested-With": "XMLHttpRequest",
        ...(options.headers || {}) // merge any headers passed in
    };
    const response = await fetch(url, {
        ...options,
        headers
    });
    if (!response.ok) {
        throw new Error('Network response was not ok');
    }
    return await response.json();
}