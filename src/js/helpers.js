export default (() => {
// Fetch all the forms we want to apply custom Bootstrap validation styles to
const forms = document.querySelectorAll('.needs-validation')
  
// Loop over them and prevent submission
Array.from(forms).forEach(form => {
    form.addEventListener('submit', event => {
        if (!form.checkValidity()) {
        event.preventDefault()
        event.stopPropagation()
        }

        form.classList.add('was-validated')
    }, false)
})
})();

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

export function viewableDateTimeHelper(input, format = 'datetime') {
    if (!input) return 'N/A';

    let date;

    // Handle both string and Date object inputs
    if (input instanceof Date) {
        // Normalize MySQL-style "YYYY-MM-DD HH:mm:ss" string
        date = input;
    } else if (typeof input === 'string') {
        date = new Date(input.replace(' ', 'T'));
    } else if (typeof input === 'number') {
        date = new Date(input);
    } else {
        return 'Invalid input';
    };

    if (isNaN(date)) return 'Invalid date';

    // Choose formatting options based on desired output
    let options;
    switch (format) {
        case 'date':
            options = {
                year: 'numeric',
                month: '2-digit',
                day: '2-digit'
            };
            return date.toLocaleDateString('en-us', options);
        case 'time':
            options = {
                hour: '2-digit',
                minute: '2-digit',
                hour12: true
            };
            return date.toLocaleTimeString('en-us', options);
        case 'datetime':
        default:
            options = {
                year: 'numeric', 
                month: '2-digit', 
                day: '2-digit', 
                hour: '2-digit', 
                minute: '2-digit', 
                hour12: true
            };
            return date.toLocaleString('en-us', options);
    }
};

export function showFlashAlert(type = 'info', message = '') {
    if (!message) return;

    const existing = document.getElementById('flash-alert');
    if (existing) existing.remove();

    const iconMap = {
        success: 'fa-thumbs-up',
        warning: 'fa-circle-radiation',
        danger: 'fa-radiation',
        info: 'fa-circle-info',
        error: 'fa-thumbs-down',
        validate: 'fa-circle-exclamation',
        default: 'fa-circle-info'
    };

    const icon = iconMap[type] || iconMap.default;

    const alertDiv = document.createElement('div');
    alertDiv.id = 'flash-alert';
    alertDiv.className = `my-2 alert alert-${type} alert-dismissible fade show`;
    alertDiv.role = 'alert';
    alertDiv.innerHTML = `
        <i class="fs-5 me-2 fa-solid ${icon}"></i>
        <span class="fs-5">${message}</span>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;

    // Append inside banner alert area
    const alertContainer = document.querySelector('#alert-container');
    if (alertContainer) {
        alertContainer.innerHTML = ''; // clear old one if needed
        alertContainer.appendChild(alertDiv);
    } else {
        // fallback if banner missing
        document.body.prepend(alertDiv);
    }
};