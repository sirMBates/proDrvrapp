export function initProfilePictureHandler(options) {
    const {
        profileInput,
        profileImage,
        drvrToken,
        getDriver,
        defaultProfileImage,
        Validation,
        drvrAlert
    } = options;

    profileInput.addEventListener('change', (e) => {
            profileImage.src = defaultProfileImage;
            const file = e.target.files[0];
            if (!file) return;
    
            // Validate the file
            const isValid = Validation.validate(file, 'file'); 
            if (!isValid) {
                    drvrAlert('error', 'Please select a valid image file (JPG, JPEG, PNG, GIF) and ensure it is within the size limit.');
                    profileImage.src = defaultProfileImage;
                    return;
            }
    
            // Read file for preview
            const reader = new FileReader();
            reader.onload = (ev) => {
                    // Show the preview
                    profileImage.src = ev.target.result;
    
                    // Prepare form data for upload
                    const formData = new FormData();
                    formData.append('profileImage', file);
                    formData.append('drvrtoken', drvrToken);
                    formData.append('__method', 'PATCH');
    
                    // Upload to server
                    getDriver('https://prodriver.local/setprofilepicture', {
                            mode: 'cors',
                            credentials: 'include',
                            method: 'POST',
                            headers: { 
                                    'X-CSRF-Token': drvrToken
                            }, // if needed
                            body: formData,
                    })
                    //.then(response => response.text())
                    .then(data => {
                            if (data.status === 'success') {
                                    drvrAlert(data.status, data.message);
                                    // Reset input after successful upload
                                    profileInput.value = '';
                                    // Reload the image that was actually saved by PHP
                                    profileImage.src = `/setprofilepicture?t=${Date.now()}`;
                            } else {
                                    drvrAlert(data.status, data.message);
                            }
                    })
                    .catch(error => console.error('Error uploading image:', error));
            };
    
            reader.onerror = () => {
                    console.error('Error reading file:', reader.error);
            };
            // Read as base64 to preview in <img>
            reader.readAsDataURL(file);
    });
};