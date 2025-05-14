export const printCurrentPage = {
    printPage (element) {
        const drvrMenu = document.querySelector('#useraccess');
        element.addEventListener('click', (e) => {
            e.preventDefault;
            const navbar = document.querySelector('nav');
            window.addEventListener('beforeprint', () => {
                $(navbar).addClass('d-none');
                $(drvrMenu).offcanvas('toggle');
            })
            window.addEventListener('afterprint', () => {
                $(navbar).removeClass('d-none');
            })
            window.print();
            //console.log('The print link was Clicked.');
        })
    }
};