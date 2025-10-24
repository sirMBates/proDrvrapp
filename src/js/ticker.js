const ticker = document.querySelector('#counter');
let count = 0;
const adder = document.querySelector('#adder');
const reset = document.querySelector('#reset');

function uptickCounter () {
    count++;
    ticker.textContent = count;
};

function resetCounter () {
    count = 0;
    ticker.textContent = count;
};

adder.addEventListener('click', uptickCounter, false);
reset.addEventListener('click', resetCounter, false);