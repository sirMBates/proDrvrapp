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
  // Merge headers safely
  const headers = {
    "X-Requested-With": "XMLHttpRequest",
    ...(options.headers || {})
  };

  // 🧩 Auto-set Content-Type if body is a stringified JSON object
  if (options.body && typeof options.body === "string" && !headers["Content-Type"]) {
    // Try to detect if it looks like JSON
    if (options.body.trim().startsWith("{") || options.body.trim().startsWith("[")) {
      headers["Content-Type"] = "application/json";
    }
  }

  const response = await fetch(url, {
    ...options,
    headers
  });

  if (!response.ok) {
    console.error("fetchDrvr failed: ", url, response.status);
    throw new Error("Network response was not ok");
  }

  // Some endpoints may not return JSON (e.g., empty 204 response)
  const text = await response.text();
  try {
    return JSON.parse(text);
  } catch {
    return text; // fallback for non-JSON replies
  }
};

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

export function showFlashAlert(type = 'info', message = '', timeout = 4000, useBanner = false) {
    if (!message) return;

    const iconMap = {
        success: 'fa-thumbs-up',
        warning: 'fa-circle-radiation',
        danger: 'fa-radiation',
        info: 'fa-circle-info',
        error: 'fa-thumbs-down',
        validate: 'fa-circle-exclamation',
        default: 'fa-circle-question'
    };

    const alertTypeMap = {
        success: 'success',
        warning: 'warning',
        danger: 'danger',
        error: 'dark',
        info: 'info',
        validate: 'primary',
        default: 'warning'
    };

    const alertClass = alertTypeMap[type] || alertTypeMap.default;
    const icon = iconMap[type] || iconMap.default;

    if (useBanner) {
        // Banner alert (top of page)
        const alertContainer = document.querySelector('#alert-container');
        if (!alertContainer) {
            console.warn('Banner alert container not found.');
            return;
        }

        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${alertClass} alert-dismissible fade show my-2`;
        alertDiv.role = 'alert';
        alertDiv.innerHTML = `
            <i class="fs-5 me-2 fa-solid ${icon}"></i>
            <span class="fs-5">${message}</span>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;

        alertContainer.innerHTML = ''; // Clear previous
        alertContainer.appendChild(alertDiv);

        // Auto-remove after timeout
        setTimeout(() => {
            if (alertDiv) {
                alertDiv.classList.remove('show');
                alertDiv.classList.add('fade');
                setTimeout(() => alertDiv.remove(), 500);
            }
        }, timeout);

    } else {
        // Toast alert (top-right)
        let container = document.querySelector('#toast-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'toast-container';
            container.style.position = 'fixed';
            container.style.top = '1rem';
            container.style.right = '1rem';
            container.style.zIndex = 1050;
            container.style.display = 'flex';
            container.style.flexDirection = 'column';
            container.style.gap = '0.5rem';
            document.body.appendChild(container);
        }

        const toast = document.createElement('div');
        toast.className = `alert alert-${alertClass} d-flex align-items-center shadow`;
        toast.style.minWidth = '250px';
        toast.style.opacity = 0;
        toast.style.transform = 'translateX(100%)';
        toast.style.transition = 'transform 0.5s ease, opacity 0.5s ease';
        toast.innerHTML = `
            <i class="fs-5 me-2 fa-solid ${icon}"></i>
            <div class="flex-grow-1">${message}</div>
            <button type="button" class="btn-close ms-2" aria-label="Close"></button>
        `;

        toast.querySelector('.btn-close').addEventListener('click', () => {
            toast.style.opacity = 0;
            toast.style.transform = 'translateX(100%)';
            setTimeout(() => toast.remove(), 500);
        });

        container.appendChild(toast);

        // Animate in
        requestAnimationFrame(() => {
            toast.style.opacity = 1;
            toast.style.transform = 'translateX(0)';
        });

        // Auto remove after timeout
        setTimeout(() => {
            toast.style.opacity = 0;
            toast.style.transform = 'translateX(100%)';
            setTimeout(() => toast.remove(), 500);
        }, timeout);
    }
};

export function fadeOut(element, duration = 400) {
    return new Promise(resolve => {
        element.style.transition = `opacity ${duration}ms ease`;
        element.style.opacity = 0;
        setTimeout(() => {
            element.style.display = 'none';
            resolve();
        }, duration);
    });
};

export function fadeIn(element, duration = 400) {
    return new Promise(resolve => {
        element.style.display = '';
        requestAnimationFrame(() => {
            element.style.transition = `opacity ${duration}ms ease`;
            element.style.opacity = 1;
        });
        setTimeout(resolve, duration);
    });
};

export class ServiceTimeCalculator {
    /**
    * Returns the millisecond difference between two timestamps.
    * @param {Date|string|number} start - Start time (Date object or parsable date string)
    * @param {Date|string|number} end - End time (Date object or parsable date string)
    * @param {number} Time difference in milliseconds
    */

    static difference(start, end) {
        const startMS = new Date(start).getTime();
        const endMS = new Date(end).getTime();
        return endMS - startMS;
    };

    /**
     * Converts a millisecond duration into "HH:MM" format.
     * @param {number} ms - Duration in milliseconds 
     * @returns {string} Formatted hours and minutes string
     */

    static toHourMinute(ms) {
        const hours = Math.floor(ms / 1000 / 60 / 60);
        const minutes = Math.floor((ms / 1000 / 60) % 60);
        return `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}`;
    };

    /**
    * Converts an "HH:MM" time string into a decimal (e.g., "7:30" → 7.50)
    * @param {string} timeStr - "HH:MM"
    * @returns {number} Time as decimal hours, rounded to 2 decimals
    */

    static toDecimal(timeStr) {
        const [hh, mm] = timeStr.split(':').map(v => parseInt(v, 10));
        return parseFloat((hh + mm / 60).toFixed(2));
    };

    static combineDateAndTime(dateStr, timeStr) {
        // Example: dateStr = "2025-11-05", timeStr = "03:00"
        // Normalize both
        if (!dateStr || !timeStr) return null;
        const combined = `${dateStr.trim()}T${timeStr.trim()}`;
        return new Date(combined);
    };

    /**
    * Handles cross-midnight adjustment if end < start (next day scenario)
    */

    static adjustForOvernight(startDateObj, endDateObj) {
        if (endDateObj < startDateObj) {
            // Add 1 day to end time
            endDateObj.setDate(endDateObj.getDate() + 1);
        }
        return endDateObj;
    };

    /**
    * Full utility: takes two timestamps and returns both HH:MM and decimal format.
    * @param {Date|string|number} start - Start time
    * @param {Date|string|number} end - End time
    * @returns {{ formatted: string, decimal: number }}
    */

    static getTotalHours(start, end) {
        const diff = this.difference(start, end);
        const formatted = this.toHourMinute(diff);
        const decimal = this.toDecimal(formatted);
        return { formatted, decimal };
    };
};