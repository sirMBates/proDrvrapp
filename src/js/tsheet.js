const clickCells = document.querySelectorAll('.editable-data');

window.addEventListener('DOMContentLoaded', () => {
    clickCells.forEach(cell => {
        cell.addEventListener('click', () => {
            if (!cell.querySelector('input') && !cell.querySelector('select')) {
                const inputConfigs = JSON.parse(cell.dataset.inputs || '[]');
                cell.textContent = '';

                const inputElements = [];

                inputConfigs.forEach((config, index) => {
                    let input;

                    switch (config.type) {
                        case 'time':
                            input = document.createElement('input');
                            input.type = 'time';
                            input.classList.add('form-control');
                            input.value = config.value || '';
                            break;

                        case 'number':
                            input = document.createElement('input');
                            input.type = 'number';
                            input.classList.add('form-control');
                            input.value = config.value || '';
                            break;

                        case 'yesno':
                            const yesLabel = document.createElement('label');
                            const yesCheckbox = document.createElement('input');
                            yesCheckbox.type = 'checkbox';
                            yesCheckbox.checked = config.value === 'Yes';
                            yesCheckbox.classList.add('form-check-input', 'yes-box', 'me-1');
                            yesLabel.appendChild(yesCheckbox);
                            yesLabel.appendChild(document.createTextNode('Yes'));

                            const noLabel = document.createElement('label');
                            const noCheckbox = document.createElement('input');
                            noCheckbox.type = 'checkbox';
                            noCheckbox.checked = config.value === 'No';
                            noCheckbox.classList.add('form-check-input', 'no-Box', 'me-1');
                            noLabel.appendChild(noCheckbox);
                            noLabel.appendChild(document.createTextNode('No'));

                            // Ensure only one checkbox is selected
                            yesCheckbox.addEventListener('change', () => {
                                if (yesCheckbox.checked) noCheckbox.checked = false;
                            });
                            noCheckbox.addEventListener('change', () => {
                                if (noCheckbox.checked) yesCheckbox.checked = false;
                            });

                            input = document.createElement('div');
                            input.appendChild(yesLabel);
                            input.appendChild(noLabel);
                            input.classList.add('d-flex', 'gap-3');
                            break;

                        case 'date':
                            input = document.createElement('input');
                            input.type = 'date';
                            input.classList.add('form-control');
                            input.value = config.value || '';
                            break;

                        case 'datetime-local':
                            input = document.createElement('input');
                            input.type = 'datetime-local';
                            input.classList.add('form-control');
                            input.value = config.value || '';
                            break;
                        
                        case 'textarea':
                            input = document.createElement('textarea');
                            input.classList.add('form-control');
                            input.value = config.value || '';
                            input.rows = config.rows || 3;
                            break;

                        default:
                            input = document.createElement('input');
                            input.type = 'text';
                            input.classList.add('form-control');
                            input.value = config.value || '';
                            break;
                    }

                    input.dataset.index = index;
                    inputElements.push(input);
                    cell.appendChild(input);
                });

                function validateInput(input, config) {
                    const value = input.value.trim();

                    switch (config.type) {
                        case 'text':
                        case 'textarea':
                            return value.length > 0;

                        case 'number':
                            return !isNaN(value) && value !== '';

                        case 'date':
                        case 'datetime-local':
                            return !isNaN(Date.parse(value));

                        case 'time':
                            return /^([01]\d|2[0-3]):([0-5]\d)$/.test(value);

                        case 'yesno':
                            const yesChecked = input.querySelector('.yes-checkbox')?.checked;
                            const noChecked = input.querySelector('.no-checkbox')?.checked;
                            return yesChecked || noChecked;

                        default:
                            return true;
                    }
                }

                // Focus first input
                if (inputElements[0]) inputElements[0].focus();

                // Blur handler for all inputs
                inputElements.forEach(input => {
                    input.addEventListener('blur', () => {
                        const isValid = inputElements.every((el, i) => validateInput(el, inputConfigs[i]));

                        if (!isValid) {
                            cell.textContent = 'Invalid input';
                            cell.classList.add('text-danger');
                            return;
                        }

                        cell.classList.remove('text-danger');
                        const newValues = inputElements.map((el, i) => {
                            const config = inputConfigs[i];

                            if (config.type === 'yesno') {
                                const yesChecked = el.querySelector('.yes-box')?.checked;
                                const noChecked = el.querySelector('no-box')?.checked;
                                return yesChecked ? 'Yes' : noChecked ? 'No' : '';
                            }

                            if (el.type === 'checkbox') {
                                return el.checked ? 'true' : 'false';
                            }

                            return el.value.trim();
                        });
                        cell.textContent = newValues.join(' | ');
                        cell.dataset.inputs = JSON.stringify(
                            inputElements.map((el, i) => {
                                const config = inputConfigs[i];
                                let value;
                                if (config.type === 'yesno') {
                                    const yesChecked = el.querySelector('yes-box')?.checked;
                                    const noChecked = el.querySelector('no-box')?.checked;
                                    value = yesChecked ? 'Yes' : noChecked ? 'No' : '';
                                } else if (el.type === 'checkbox') {
                                    value = el.checked;
                                } else {
                                    value = el.value.trim();
                                }
                                return {
                                    type: config.type,
                                    value,
                                    options: config.options || undefined,
                                    rows: config.rows || undefined
                                };
                            })
                        );
                    });

                    input.addEventListener('keydown', (e) => {
                        if (e.key === 'Enter' && input.tagName !== 'TEXTAREA') {
                            input.blur();
                        }
                    });
                });
            }
        });
    });
    const screenSize = window.innerWidth;
    const cardFooter = document.querySelector('.card-footer');
    const cardFooterChildren = cardFooter.children;
    if (screenSize <= 630) {
        for (const child of cardFooterChildren) {
            if (child.classList.contains('row')) {
                child.classList.remove('col-lg-10');
                child.classList.add('col-12');
            }
        }
    }
});
