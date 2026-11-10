
const clockContainer = document.querySelector('#clock_container');

if (clockContainer) {
    const dateParts = clockContainer.querySelectorAll('#dateCon > div');
    const timeParts = clockContainer.querySelectorAll('#timeCon > div');

    if (dateParts.length === 4 && timeParts.length === 5) {
        const [dayOfWeek, month, dayDate, year] = dateParts;
        const [timeHr, separator, timeMins, seconds, meridiem] = timeParts;

        const updateClock = () => {
            const now = new Date();

            const weekday = now.toLocaleDateString('en-US', {
                weekday: 'short'
            });

            const monthName = now.toLocaleDateString('en-US', {
                month: 'short'
            });

            const day = now.getDate();
            const yearValue = now.getFullYear();

            let hours = now.getHours();
            const minutes = String(now.getMinutes()).padStart(2, '0');
            const period = hours >= 12 ? 'PM' : 'AM';

            hours = hours % 12 || 12;

            dayOfWeek.textContent = weekday;
            month.textContent = monthName;
            dayDate.textContent = `${day},`;
            year.textContent = yearValue;

            timeHr.textContent = String(hours).padStart(2, '0');
            timeMins.textContent = minutes;
            meridiem.textContent = period;
        };

        updateClock();
        setInterval(updateClock, 1000);
    }
};
