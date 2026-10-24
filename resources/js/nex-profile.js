document.addEventListener('DOMContentLoaded', () => {

    /* =====================================================
       PROFILE PHOTO PREVIEW
    ===================================================== */

    const photoInput = document.getElementById('profile_photo');
    const preview = document.getElementById('profilePreview');
    const placeholder = document.getElementById('profilePreviewPlaceholder');
    const coverInput = document.getElementById('cover_photo');
    const coverPreview = document.getElementById('coverPreview');

    if (coverInput && coverPreview) {
        coverInput.addEventListener('change', function () {
            const file = this.files[0];
            if (!file) return;
            if (!file.type.startsWith('image/')) {
                alert('Please select an image file.');
                this.value = '';
                return;
            }
            coverPreview.style.backgroundImage = `url("${URL.createObjectURL(file)}")`;
        });
    }

    if (photoInput) {

        photoInput.addEventListener('change', function () {

            const file = this.files[0];

            if (!file) {
                return;
            }

            if (!file.type.startsWith('image/')) {
                alert('Please select an image file.');
                this.value = '';
                return;
            }

            const reader = new FileReader();

            reader.onload = function (event) {

                if (preview) {
                    preview.src = event.target.result;
                    preview.style.display = 'block';
                } else {

                    const img = document.createElement('img');

                    img.id = 'profilePreview';
                    img.className = 'nex-photo-preview';
                    img.src = event.target.result;
                    img.alt = 'Profile preview';

                    if (placeholder) {
                        placeholder.replaceWith(img);
                    }
                }

            };

            reader.readAsDataURL(file);

        });

    }


    /* =====================================================
       BIO COUNTER
    ===================================================== */

    const bio = document.getElementById('bio');
    const bioCounter = document.getElementById('bioCounter');

    if (bio && bioCounter) {

        const updateCounter = () => {
            bioCounter.textContent =
                `${bio.value.length}/160`;
        };

        bio.addEventListener('input', updateCounter);

        updateCounter();
    }


    /* =====================================================
       SAVE BUTTON LOADING STATE
    ===================================================== */

    const form = document.querySelector('.nex-profile-form');
    const saveButton = document.querySelector('.nex-save-button');

    if (form && saveButton) {

        form.addEventListener('submit', () => {

            saveButton.disabled = true;

            saveButton.innerHTML = `
                <i class="fa-solid fa-spinner fa-spin"></i>
                Saving...
            `;

        });

    }

});
