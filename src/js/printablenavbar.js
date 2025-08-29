import { printCurrentPage } from './print.js';
import html2pdf from 'html2pdf.js';
const drvrMenu = document.querySelector('#useraccess');
const menuCardOpts = drvrMenu.childNodes[3].childNodes[5];
const printLink = menuCardOpts.childNodes[5].childNodes[1];
const downloadLink = printLink.nextElementSibling;
const navbar = document.querySelector('nav');
const btnTheme = document.querySelector('#themeBtn').parentElement;

function changeDrvrMenu () {
    let currentPage = window.location.pathname;
    if (currentPage === '/printable') {
        menuCardOpts.classList.remove('d-none');
        btnTheme.classList.add('d-none');
    } else {
        menuCardOpts.classList.add('d-none');
    }
}
$(window).on('load', changeDrvrMenu);

// This is for the print btn in driver menu.
printCurrentPage.printPage(printLink);

// Save and download as pdf - feature.
function saveAndDownload (docFile) {
    let dateNow = new Date();
    docFile = document.documentElement;
    const options = {
        filename: dateNow.toLocaleString("en-us") + '.pdf',
        margin: .5,        
        image: { type: 'jpeg', quality: .95 },
        html2canvas: { scale: 2, logging: true, dpi: 192, letterRendering: true },
        jsPDF: { unit: 'in', format: 'letter', orientation: 'portrait' },
        pagebreak: { mode: 'avoid-all' }
    };
    html2pdf().set(options).from(docFile).save(); 
};
// The button to start the download and pdf feature in driver menu.
downloadLink.addEventListener('click', function () {
    navbar.style.display = 'none';
    $(drvrMenu).offcanvas('toggle');
    saveAndDownload(window.location.pathname);
    setTimeout(function() {
        if (navbar.style.display === 'none') {
            return navbar.style.display = 'block';
        }
    }, 3000)
}, false);