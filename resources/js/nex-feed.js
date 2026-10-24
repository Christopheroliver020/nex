document.addEventListener('DOMContentLoaded', function () {

    // Confirm post deletion in a centered, accessible card instead of a browser prompt.
    let postDeleteDialog = document.querySelector('[data-post-delete-dialog]');
    if (!postDeleteDialog) {
        postDeleteDialog = document.createElement('dialog');
        postDeleteDialog.className = 'nex-post-delete-dialog';
        postDeleteDialog.setAttribute('data-post-delete-dialog', '');
        postDeleteDialog.setAttribute('aria-labelledby', 'nex-post-delete-title');
        postDeleteDialog.innerHTML = '<div class="nex-post-delete-card"><span class="nex-post-delete-icon" aria-hidden="true">!</span><div><h2 id="nex-post-delete-title">Delete this post?</h2><p data-delete-description>This post will be permanently removed. You cannot undo this action.</p></div><div class="nex-post-delete-actions"><button type="button" data-delete-cancel>Cancel</button><button type="button" data-delete-confirm>Delete post</button></div></div>';
        document.body.appendChild(postDeleteDialog);
    }

    let pendingDeleteForm = null;
    const cancelDeleteButton = postDeleteDialog.querySelector('[data-delete-cancel]');
    const confirmDeleteButton = postDeleteDialog.querySelector('[data-delete-confirm]');
    document.addEventListener('submit', function (event) {
        const form = event.target.closest('[data-post-delete-form], [data-confirm-delete]');
        if (!form || form.dataset.confirmed === 'true') {
            if (form) delete form.dataset.confirmed;
            return;
        }
        event.preventDefault();
        pendingDeleteForm = form;
        postDeleteDialog.querySelector('#nex-post-delete-title').textContent = form.dataset.confirmTitle || 'Delete this post?';
        postDeleteDialog.querySelector('[data-delete-description]').textContent = form.dataset.confirmMessage || 'This post will be permanently removed. You cannot undo this action.';
        confirmDeleteButton.textContent = form.dataset.confirmAction || 'Delete post';
        if (!postDeleteDialog.open) postDeleteDialog.showModal();
    }, true);
    cancelDeleteButton.addEventListener('click', function () {
        pendingDeleteForm = null;
        postDeleteDialog.close();
    });
    confirmDeleteButton.addEventListener('click', function () {
        if (!pendingDeleteForm) return;
        const form = pendingDeleteForm;
        pendingDeleteForm = null;
        form.dataset.confirmed = 'true';
        postDeleteDialog.close();
        form.requestSubmit();
    });

    const postOptionMenus = Array.from(document.querySelectorAll('.nex-post-menu'));
    postOptionMenus.forEach(function (menu) {
        const trigger = menu.querySelector(':scope > summary');
        const panel = menu.querySelector(':scope > .nex-post-menu-panel');
        menu.addEventListener('toggle', function () {
            if (menu.open) {
                postOptionMenus.forEach(function (otherMenu) {
                    if (otherMenu !== menu) otherMenu.open = false;
                });
                panel.setAttribute('role', 'dialog');
                panel.setAttribute('aria-modal', 'true');
                panel.setAttribute('aria-label', menu.querySelector('.nex-post-edit') ? 'Post options and editing' : 'Report post');
                panel.setAttribute('tabindex', '-1');
                panel.focus({ preventScroll: true });
            } else {
                panel.removeAttribute('aria-modal');
                trigger.focus({ preventScroll: true });
            }
        });
    });
    document.addEventListener('click', function (event) {
        if (!event.target.matches('.nex-post-menu')) return;
        const backdropMenu = event.target;
        backdropMenu.open = false;
    });
    document.addEventListener('keydown', function (event) {
        if (event.key !== 'Escape') return;
        const openMenu = document.querySelector('.nex-post-menu[open]');
        if (openMenu) openMenu.open = false;
    });

    // Open post images in an accessible, responsive lightbox.
    let postImageDialog = document.querySelector('[data-post-image-dialog]');
    if (!postImageDialog) {
        postImageDialog = document.createElement('dialog');
        postImageDialog.className = 'nex-post-image-dialog';
        postImageDialog.setAttribute('data-post-image-dialog', '');
        postImageDialog.setAttribute('aria-label', 'Full-size post image');
        postImageDialog.innerHTML = '<button type="button" class="nex-post-image-close" data-image-close aria-label="Close image">×</button><img data-image-dialog-img alt="Full-size post image">';
        document.body.appendChild(postImageDialog);
    }

    const dialogImage = postImageDialog.querySelector('[data-image-dialog-img]');
    const closeImageButton = postImageDialog.querySelector('[data-image-close]');
    document.addEventListener('click', function (event) {
        const imageButton = event.target.closest('[data-image-viewer]');
        if (!imageButton) return;
        const image = imageButton.querySelector('img');
        if (!image) return;
        dialogImage.src = image.currentSrc || image.src;
        dialogImage.alt = image.alt || 'Full-size post image';
        if (!postImageDialog.open) postImageDialog.showModal();
    });
    closeImageButton.addEventListener('click', function () { postImageDialog.close(); });
    postImageDialog.addEventListener('click', function (event) {
        if (event.target === postImageDialog) postImageDialog.close();
    });
    postImageDialog.addEventListener('close', function () { dialogImage.removeAttribute('src'); });

    /*
    |--------------------------------------------------------------------------
    | Toggle Comments
    |--------------------------------------------------------------------------
    */

    window.toggleComments = function (postId) {

        const comments = document.getElementById(
            'comments-' + postId
        );

        if (!comments) {
            return;
        }

        const isHidden =
            comments.style.display === 'none' ||
            comments.style.display === '';

        comments.style.display = isHidden
            ? 'block'
            : 'none';
    };


    /*
    |--------------------------------------------------------------------------
    | Toggle Reply Form
    |--------------------------------------------------------------------------
    */

    window.toggleReply = function (commentId) {

        const replyForm = document.getElementById(
            'reply-' + commentId
        );

        if (!replyForm) {
            return;
        }

        const isHidden =
            replyForm.style.display === 'none' ||
            replyForm.style.display === '';

        replyForm.style.display = isHidden
            ? 'flex'
            : 'none';

        if (isHidden) {

            const input = replyForm.querySelector(
                'input[name="content"]'
            );

            if (input) {
                input.focus();
            }
        }
    };


    /* =========================================================
    NEX DELETE MODAL
   ========================================================= */

    document.addEventListener('DOMContentLoaded', function () {

        const modal = document.getElementById('nexDeleteModal');
        const deleteForm = document.getElementById('nexDeleteForm');

        const closeButton = document.getElementById('nexDeleteClose');
        const cancelButton = document.getElementById('nexDeleteCancel');

        const backdrop = modal
            ? modal.querySelector('.nex-delete-backdrop')
            : null;

        const title = document.getElementById('nexDeleteTitle');
        const message = document.getElementById('nexDeleteMessage');

        if (!modal || !deleteForm) {
            return;
        }


        /* =====================================================
        OPEN MODAL
        ===================================================== */

        document.querySelectorAll('.nex-open-delete-modal')
            .forEach(function (button) {

                button.addEventListener('click', function () {

                    const deleteUrl =
                        button.getAttribute('data-delete-url');

                    const deleteType =
                        button.getAttribute('data-delete-type');


                    /* Set form action */

                    deleteForm.setAttribute(
                        'action',
                        deleteUrl
                    );


                    /* Change text */

                    if (deleteType === 'post') {

                        title.textContent =
                            'Delete this post?';

                        message.textContent =
                            'This action cannot be undone. Your post will be permanently removed from NEX.';

                    } else {

                        title.textContent =
                            'Delete this item?';

                        message.textContent =
                            'This action cannot be undone.';

                    }


                    /* Show modal */

                    modal.classList.add('active');

                    modal.setAttribute(
                        'aria-hidden',
                        'false'
                    );

                    document.body.classList.add(
                        'nex-modal-open'
                    );


                    /* Focus cancel button */

                    setTimeout(function () {

                        cancelButton.focus();

                    }, 100);

                });

            });


        /* =====================================================
        CLOSE MODAL
        ===================================================== */

        function closeDeleteModal() {

            modal.classList.remove('active');

            modal.setAttribute(
                'aria-hidden',
                'true'
            );

            document.body.classList.remove(
                'nex-modal-open'
            );


            /* Clear form action */

            deleteForm.setAttribute(
                'action',
                ''
            );

        }


        /* =====================================================
        CLOSE BUTTON
        ===================================================== */

        closeButton.addEventListener(
            'click',
            closeDeleteModal
        );


        /* =====================================================
        CANCEL BUTTON
        ===================================================== */

        cancelButton.addEventListener(
            'click',
            closeDeleteModal
        );


        /* =====================================================
        CLICK BACKDROP
        ===================================================== */

        if (backdrop) {

            backdrop.addEventListener(
                'click',
                closeDeleteModal
            );

        }


        /* =====================================================
        ESC KEY
        ===================================================== */

        document.addEventListener(
            'keydown',
            function (event) {

                if (
                    event.key === 'Escape' &&
                    modal.classList.contains('active')
                ) {

                    closeDeleteModal();

                }

            }
        );

    });


    /*
    |--------------------------------------------------------------------------
    | Auto Hide Success Message
    |--------------------------------------------------------------------------
    */

    const successMessage = document.querySelector(
        '.nex-success-message'
    );

    if (successMessage) {

        setTimeout(function () {

            successMessage.style.opacity = '0';

            setTimeout(function () {
                successMessage.remove();
            }, 300);

        }, 3000);
    }


    /*
    |--------------------------------------------------------------------------
    | Media File Selection
    |--------------------------------------------------------------------------
    */

    document.querySelectorAll('.nex-post-form').forEach(function (form) {
        const mediaInputs = Array.from(form.querySelectorAll('[data-post-media-input]'));
        const preview = form.querySelector('[data-post-media-preview]');
        const stage = form.querySelector('[data-post-media-stage]');
        const name = form.querySelector('[data-post-media-name]');
        const removeButton = form.querySelector('[data-post-media-remove]');
        let previewUrl = null;
        let selectedInput = null;

        function clearPreview() {
            if (previewUrl) URL.revokeObjectURL(previewUrl);
            previewUrl = null;
            stage.replaceChildren();
            name.textContent = '';
            preview.hidden = true;
        }

        mediaInputs.forEach(function (input) {
            input.addEventListener('change', function () {
                const file = input.files && input.files[0];
                if (!file) return;

                mediaInputs.forEach(function (otherInput) {
                    if (otherInput !== input) otherInput.value = '';
                    const label = otherInput.closest('label');
                    if (label) label.classList.toggle('selected', otherInput === input);
                });

                selectedInput = input;
                clearPreview();
                previewUrl = URL.createObjectURL(file);
                name.textContent = file.name;
                preview.hidden = false;

                if (file.type.startsWith('video/')) {
                    const video = document.createElement('video');
                    video.src = previewUrl;
                    video.controls = true;
                    video.playsInline = true;
                    video.preload = 'metadata';
                    stage.appendChild(video);
                } else if (file.type.startsWith('image/')) {
                    const image = document.createElement('img');
                    image.src = previewUrl;
                    image.alt = 'Selected post image preview';
                    stage.appendChild(image);
                }
            });
        });

        removeButton.addEventListener('click', function () {
            if (selectedInput) selectedInput.value = '';
            mediaInputs.forEach(function (input) {
                const label = input.closest('label');
                if (label) label.classList.remove('selected');
            });
            selectedInput = null;
            clearPreview();
        });
    });

    // Show transfer progress for text-only, image, and video posts.
    document.querySelectorAll('.nex-post-form').forEach(function (form) {
        const progress = form.querySelector('[data-post-progress]');
        const ring = form.querySelector('[data-post-progress-ring]');
        const percentText = form.querySelector('[data-post-progress-percent]');
        const title = form.querySelector('[data-post-progress-title]');
        const detail = form.querySelector('[data-post-progress-detail]');
        const fill = form.querySelector('[data-post-progress-fill]');
        const progressBar = form.querySelector('[role="progressbar"]');
        const button = form.querySelector('.nex-post-button');
        const error = form.querySelector('[data-post-upload-error]');
        const errorMessage = form.querySelector('[data-post-error-message]');
        const retryButton = form.querySelector('[data-post-retry]');

        function startPostUpload() {
            if (button.disabled) return;
            if (!navigator.onLine) {
                error.hidden = false;
                retryButton.hidden = false;
                retryButton.disabled = true;
                errorMessage.textContent = 'You are offline. Reconnect, then retry your post.';
                return;
            }
            error.hidden = true;
            retryButton.hidden = true;
            retryButton.disabled = false;
            errorMessage.textContent = '';
            progress.hidden = false;
            button.disabled = true;
            button.textContent = 'Publishing…';
            title.textContent = 'Publishing your post…';
            detail.textContent = 'Your text and media are being sent to NEX.';
            fill.style.width = '0%';
            ring.style.setProperty('--progress', '0%');
            percentText.textContent = '0%';
            progressBar.setAttribute('aria-valuenow', '0');

            const request = new XMLHttpRequest();
            request.open('POST', form.action, true);
            request.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
            request.setRequestHeader('Accept', 'application/json');

            request.upload.addEventListener('progress', function (uploadEvent) {
                if (!uploadEvent.lengthComputable) return;
                const percent = Math.min(100, Math.round((uploadEvent.loaded / uploadEvent.total) * 100));
                fill.style.width = percent + '%';
                ring.style.setProperty('--progress', percent + '%');
                percentText.textContent = percent + '%';
                progressBar.setAttribute('aria-valuenow', String(percent));
                if (percent >= 100) {
                    title.textContent = 'Upload complete';
                    detail.textContent = 'NEX is publishing your post…';
                    button.textContent = 'Publishing…';
                }
            });

            function failUpload(message, canRetry) {
                errorMessage.textContent = message;
                error.hidden = false;
                retryButton.hidden = !canRetry;
                retryButton.disabled = canRetry && !navigator.onLine;
                progress.hidden = true;
                button.disabled = false;
                button.textContent = 'Post';
            }

            request.addEventListener('load', function () {
                if (request.status >= 200 && request.status < 400) {
                    window.location.assign(request.responseURL || window.location.href);
                    return;
                }

                let message = 'Your post could not be published. Please try again.';
                if (request.status === 413) {
                    message = 'This upload is larger than the server allows. Choose a photo or video under 35 MB.';
                } else if (request.status === 419) {
                    message = 'Your session expired before the post was sent. Refresh the page, then try again.';
                } else if (request.status >= 500) {
                    message = 'NEX could not publish the post because of a server error. Your text and selected media are still here; you can retry.';
                }
                try {
                    const response = JSON.parse(request.responseText);
                    if (request.status !== 413 && request.status !== 419 && response.errors) message = Object.values(response.errors).flat().join(' ');
                    else if (request.status !== 413 && request.status !== 419 && response.message) message = response.message;
                } catch (parseError) {}
                failUpload(message, request.status >= 500);
            });

            request.addEventListener('error', function () {
                failUpload(navigator.onLine
                    ? 'Network error while publishing. Your selected text and media are kept; retry to send the post again.'
                    : 'Connection lost. Your selected text and media are kept. Reconnect, then retry the upload.', true);
            });

            request.send(new FormData(form));
        }

        form.addEventListener('submit', function (event) {
            event.preventDefault();
            if (!form.reportValidity()) return;
            startPostUpload();
        });

        retryButton.addEventListener('click', startPostUpload);
        window.addEventListener('online', function () {
            if (error.hidden || retryButton.hidden) return;
            retryButton.disabled = false;
            errorMessage.textContent = 'You are back online. Retry to send your post again.';
        });
        window.addEventListener('offline', function () {
            if (error.hidden || retryButton.hidden) return;
            retryButton.disabled = true;
            errorMessage.textContent = 'Connection lost. Reconnect, then retry the upload.';
        });
    });


    /*
    |--------------------------------------------------------------------------
    | Prevent Empty Comments
    |--------------------------------------------------------------------------
    */

    document.querySelectorAll(
        '.nex-comment-form, .nex-reply-form'
    ).forEach(function (form) {

        form.addEventListener('submit', function (event) {

            const input = form.querySelector(
                'input[name="content"]'
            );

            if (!input) {
                return;
            }

            if (input.value.trim() === '') {

                event.preventDefault();

                input.focus();
            }

        });

    });


    /*
    |--------------------------------------------------------------------------
    | Save Button
    |--------------------------------------------------------------------------
    */

    document.querySelectorAll('.nex-save').forEach(function (button) {

        button.addEventListener('click', function () {

            this.classList.toggle('active');

        });

    });

    // Live people search, shared by desktop navigation and the mobile menu.
    document.querySelectorAll('.nex-search').forEach(function (search) {
        const input = search.querySelector('.nex-search-input');
        const results = search.querySelector('.nex-search-results');
        if (!input || !results) return;

        let timer;
        let controller;
        let activeIndex = -1;

        function closeResults() {
            results.classList.remove('is-open');
            results.replaceChildren();
            input.setAttribute('aria-expanded', 'false');
            activeIndex = -1;
        }

        search.addEventListener('click', function (event) {
            if (!search.classList.contains('nex-search-bottom') || results.contains(event.target)) return;
            search.classList.add('is-search-active');
            if (event.target !== input) input.focus({ preventScroll: true });
        });
        input.addEventListener('focus', function () {
            if (search.classList.contains('nex-search-bottom')) search.classList.add('is-search-active');
        });

        function showMessage(message) {
            const item = document.createElement('div');
            item.className = 'nex-search-empty';
            item.textContent = message;
            results.replaceChildren(item);
            results.classList.add('is-open');
            input.setAttribute('aria-expanded', 'true');
        }

        function renderUsers(users) {
            results.replaceChildren();
            if (!users.length) {
                showMessage('No people found.');
                return;
            }

            users.forEach(function (user) {
                const link = document.createElement('a');
                link.className = 'nex-search-result';
                link.href = user.url;
                link.setAttribute('role', 'option');

                if (user.photo) {
                    const avatar = document.createElement('img');
                    avatar.src = user.photo;
                    avatar.alt = '';
                    link.appendChild(avatar);
                } else {
                    const avatar = document.createElement('span');
                    avatar.className = 'nex-search-avatar';
                    avatar.textContent = (user.name || '?').trim().charAt(0).toUpperCase();
                    link.appendChild(avatar);
                }

                const copy = document.createElement('span');
                copy.className = 'nex-search-result-copy';
                const name = document.createElement('strong');
                name.textContent = user.name;
                const username = document.createElement('small');
                username.textContent = '@' + user.username;
                copy.append(name, username);
                link.appendChild(copy);
                results.appendChild(link);
            });
            results.classList.add('is-open');
            input.setAttribute('aria-expanded', 'true');
        }

        input.addEventListener('input', function () {
            clearTimeout(timer);
            if (controller) controller.abort();
            const query = input.value.trim();
            if (query.length < 2) {
                closeResults();
                return;
            }

            timer = setTimeout(async function () {
                controller = new AbortController();
                showMessage('Searching…');
                try {
                    const endpoint = new URL(search.dataset.searchUrl, window.location.origin);
                    endpoint.searchParams.set('q', query);
                    const response = await fetch(endpoint, {
                        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                        signal: controller.signal,
                    });
                    if (!response.ok) throw new Error('Search request failed');
                    renderUsers(await response.json());
                } catch (error) {
                    if (error.name !== 'AbortError') showMessage('Search is unavailable. Please try again.');
                }
            }, 180);
        });

        input.addEventListener('keydown', function (event) {
            const links = Array.from(results.querySelectorAll('.nex-search-result'));
            if (event.key === 'Escape') {
                closeResults();
                search.classList.remove('is-search-active');
                input.blur();
            } else if (event.key === 'ArrowDown' && links.length) {
                event.preventDefault();
                activeIndex = (activeIndex + 1) % links.length;
                links[activeIndex].focus();
            }
        });
        results.addEventListener('keydown', function (event) {
            const links = Array.from(results.querySelectorAll('.nex-search-result'));
            if (!links.length) return;
            if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
                event.preventDefault();
                activeIndex = (activeIndex + (event.key === 'ArrowDown' ? 1 : links.length - 1)) % links.length;
                links[activeIndex].focus();
            } else if (event.key === 'Escape') {
                closeResults();
                search.classList.remove('is-search-active');
                input.blur();
            }
        });
        document.addEventListener('click', function (event) {
            if (!search.contains(event.target)) {
                closeResults();
                search.classList.remove('is-search-active');
            }
        });
    });

    // Toggle post reactions in place so the heart changes immediately.
    document.querySelectorAll('.nex-react-form').forEach(function (form) {
        form.addEventListener('submit', async function (event) {
            event.preventDefault();
            const button = form.querySelector('.nex-react-button');
            const card = form.closest('.nex-post-card, .nex-reel-card');
            if (!button || !card || button.disabled) return;

            button.disabled = true;
            button.classList.add('is-loading');
            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    body: new FormData(form),
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    credentials: 'same-origin',
                });
                if (!response.ok) throw new Error('Could not update reaction');

                const state = await response.json();
                button.classList.toggle('is-reacted', state.reacted);
                button.setAttribute('aria-pressed', state.reacted ? 'true' : 'false');
                button.setAttribute('aria-label', state.reacted ? 'Unlike this post' : 'Like this post');

                const count = card.querySelector('.nex-reaction-count');
                const badge = card.querySelector('.nex-reaction-icon');
                if (count) {
                    count.textContent = state.count;
                    count.hidden = state.count < 1;
                }
                if (badge) badge.hidden = state.count < 1;
            } catch (error) {
                // Preserve the regular form behavior if the asynchronous request fails.
                form.submit();
            } finally {
                button.disabled = false;
                button.classList.remove('is-loading');
            }
        });
    });

    document.querySelectorAll('.nex-reel-comment-react').forEach(function (form) {
        form.addEventListener('submit', async function (event) {
            event.preventDefault();
            const button = form.querySelector('button');
            if (!button || button.disabled) return;

            button.disabled = true;
            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    body: new FormData(form),
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    credentials: 'same-origin',
                });
                if (!response.ok) throw new Error('Could not update comment reaction');

                const state = await response.json();
                button.classList.toggle('is-reacted', state.reacted);
                button.setAttribute('aria-pressed', state.reacted ? 'true' : 'false');
                const label = button.querySelector('.nex-reel-comment-reaction-label');
                const count = button.querySelector('.nex-reel-comment-reaction-count');
                if (label) label.textContent = state.reacted ? 'Liked' : 'Like';
                if (count) {
                    count.textContent = state.count;
                    count.hidden = state.count < 1;
                }
            } catch (error) {
                form.submit();
            } finally {
                button.disabled = false;
            }
        });
    });

    // Share externally with the native share sheet when available, otherwise copy a link.
    let shareToastTimer;
    function showShareFeedback(message, isError) {
        let toast = document.querySelector('.nex-share-toast');
        if (!toast) {
            toast = document.createElement('div');
            toast.className = 'nex-share-toast';
            toast.setAttribute('role', 'status');
            toast.setAttribute('aria-live', 'polite');
            document.body.appendChild(toast);
        }
        toast.textContent = message;
        toast.classList.toggle('is-error', Boolean(isError));
        toast.classList.add('is-visible');
        clearTimeout(shareToastTimer);
        shareToastTimer = setTimeout(function () { toast.classList.remove('is-visible'); }, 3000);
    }

    async function copyShareUrl(url) {
        if (navigator.clipboard && window.isSecureContext) {
            await navigator.clipboard.writeText(url);
            return;
        }

        const temporaryInput = document.createElement('textarea');
        temporaryInput.value = url;
        temporaryInput.setAttribute('readonly', '');
        temporaryInput.style.position = 'fixed';
        temporaryInput.style.opacity = '0';
        document.body.appendChild(temporaryInput);
        temporaryInput.select();
        const copied = document.execCommand('copy');
        temporaryInput.remove();
        if (!copied) throw new Error('Could not copy the post link.');
    }

    document.querySelectorAll('.nex-share-form').forEach(function (form) {
        form.addEventListener('submit', async function (event) {
            event.preventDefault();
            const button = form.querySelector('.nex-share-button');
            const card = form.closest('.nex-post-card, .nex-reel-card');
            if (!button || !card || button.disabled) return;

            const url = form.dataset.shareUrl;
            const title = form.dataset.shareTitle || 'A post on NEX';
            button.disabled = true;
            button.classList.add('is-loading');

            try {
                let copied = false;
                if (navigator.share) {
                    try {
                        await navigator.share({ title: title, text: 'Check this out on NEX', url: url });
                    } catch (shareError) {
                        if (shareError.name === 'AbortError') return;
                        await copyShareUrl(url);
                        copied = true;
                    }
                } else {
                    await copyShareUrl(url);
                    copied = true;
                }

                const response = await fetch(form.action, {
                    method: 'POST',
                    body: new FormData(form),
                    credentials: 'same-origin',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });
                if (!response.ok) throw new Error('The link was shared, but NEX could not update the share count.');

                const state = await response.json();
                const count = Number(state.shares_count || 0);
                const reelCount = button.querySelector('.nex-share-count');
                if (reelCount) reelCount.textContent = count;

                const summary = card.querySelector('.nex-reaction-summary-text');
                if (summary && count > 0) {
                    let summaryCount = summary.querySelector('.nex-share-count-summary');
                    if (!summaryCount) {
                        summaryCount = document.createElement('span');
                        summaryCount.className = 'nex-share-count-summary';
                        summary.appendChild(summaryCount);
                    }
                    summaryCount.textContent = count + (count === 1 ? ' share' : ' shares');
                }

                showShareFeedback(copied ? 'Link copied and share recorded.' : 'Shared successfully.');
            } catch (error) {
                showShareFeedback(error.message || 'Could not share this post. Please try again.', true);
            } finally {
                button.disabled = false;
                button.classList.remove('is-loading');
            }
        });
    });

    // Reels play only while prominently visible in the reels scroller.
    const reelList = document.querySelector('.nex-reels-list');
    const reelCards = document.querySelectorAll('.nex-reel-card');

    function setReelPlayback(card, shouldPlay) {
        const video = card.querySelector('[data-reel-video]');
        const button = card.querySelector('[data-reel-toggle]');
        if (!video) return;
        if (shouldPlay) {
            video.play().then(function () {
                card.classList.remove('needs-gesture');
                if (button) {
                    button.classList.add('is-playing');
                    button.setAttribute('aria-label', 'Pause video');
                }
            }).catch(function () {
                card.classList.add('needs-gesture');
                if (button) {
                    button.classList.remove('is-playing');
                    button.setAttribute('aria-label', 'Play video with sound');
                }
            });
        } else {
            video.pause();
            card.classList.remove('needs-gesture');
            if (button) {
                button.classList.remove('is-playing');
                button.setAttribute('aria-label', 'Play video');
            }
        }
    }

    reelCards.forEach(function (card) {
        const video = card.querySelector('[data-reel-video]');
        const playButton = card.querySelector('[data-reel-toggle]');
        if (!video) return;
        video.muted = false;

        const togglePlayback = function () {
            setReelPlayback(card, video.paused);
        };
        video.addEventListener('click', togglePlayback);
        if (playButton) playButton.addEventListener('click', togglePlayback);

        const syncReelSoundControls = function () {
            card.querySelectorAll('[data-reel-sound]').forEach(function (button) {
                button.classList.toggle('is-muted', video.muted);
                button.setAttribute('aria-label', video.muted ? 'Unmute video' : 'Mute video');
                button.setAttribute('aria-pressed', video.muted ? 'false' : 'true');
            });
        };

        syncReelSoundControls();
        video.addEventListener('volumechange', syncReelSoundControls);

        card.querySelectorAll('[data-reel-sound]').forEach(function (soundButton) {
            soundButton.addEventListener('click', function (event) {
                event.preventDefault();
                event.stopPropagation();
                video.muted = !video.muted;
                syncReelSoundControls();
                if (!video.muted && video.paused) {
                    video.play().then(function () {
                        if (playButton) {
                            playButton.classList.add('is-playing');
                            playButton.setAttribute('aria-label', 'Pause video');
                        }
                    }).catch(function () {});
                }
            });
        });
    });

    if ('IntersectionObserver' in window && reelList) {
        const reelObserver = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                setReelPlayback(entry.target, entry.isIntersecting && entry.intersectionRatio >= 0.65);
            });
        }, { root: reelList, threshold: [0, 0.35, 0.65, 1] });

        reelCards.forEach(function (card) { reelObserver.observe(card); });
    }

    // Feed videos autoplay muted, loop, and pause when they leave the viewport.
    const feedVideos = Array.from(document.querySelectorAll('[data-feed-video]'));
    function formatVideoTime(seconds) {
        if (!Number.isFinite(seconds)) return '0:00';
        const wholeSeconds = Math.floor(seconds);
        return Math.floor(wholeSeconds / 60) + ':' + String(wholeSeconds % 60).padStart(2, '0');
    }
    feedVideos.forEach(function (video) {
        const player = video.closest('.nex-feed-video-player');
        const playButton = player.querySelector('[data-feed-video-toggle]');
        const soundButton = player.querySelector('[data-feed-video-sound]');
        const seek = player.querySelector('[data-feed-video-seek]');
        const currentTime = player.querySelector('[data-feed-video-current]');
        const durationTime = player.querySelector('[data-feed-video-duration]');
        function syncPlayState() {
            const playing = !video.paused;
            player.classList.toggle('is-playing', playing);
            if (playing) player.classList.remove('needs-gesture');
            playButton.setAttribute('aria-label', playing ? 'Pause video' : 'Play video');
        }
        function syncSoundState() {
            player.classList.toggle('is-muted', video.muted);
            soundButton.setAttribute('aria-label', video.muted ? 'Unmute video' : 'Mute video');
            soundButton.setAttribute('aria-pressed', video.muted ? 'false' : 'true');
        }
        function togglePlayback() {
            if (video.paused) {
                video.dataset.manuallyPaused = 'false';
                video.play().catch(function () { player.classList.add('needs-gesture'); });
            } else {
                video.dataset.manuallyPaused = 'true';
                video.pause();
            }
        }

        video.loop = true;
        video.muted = false;
        video.playsInline = true;
        playButton.addEventListener('click', togglePlayback);
        video.addEventListener('click', togglePlayback);
        soundButton.addEventListener('click', function () { video.muted = !video.muted; });
        video.addEventListener('play', syncPlayState);
        video.addEventListener('pause', syncPlayState);
        video.addEventListener('volumechange', syncSoundState);
        video.addEventListener('loadedmetadata', function () { durationTime.textContent = formatVideoTime(video.duration); });
        video.addEventListener('timeupdate', function () {
            currentTime.textContent = formatVideoTime(video.currentTime);
            const percent = video.duration ? (video.currentTime / video.duration) * 100 : 0;
            seek.value = String(percent);
            seek.style.setProperty('--seek-progress', percent + '%');
        });
        seek.addEventListener('input', function () {
            if (video.duration) video.currentTime = (Number(seek.value) / 100) * video.duration;
        });
        syncPlayState();
        syncSoundState();
    });

    if ('IntersectionObserver' in window && feedVideos.length) {
        const feedVideoObserver = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                const video = entry.target;
                if (entry.isIntersecting && entry.intersectionRatio >= 0.6) {
                    video.dataset.inViewport = 'true';
                    if (video.dataset.manuallyPaused !== 'true') {
                        video.play().catch(function () { video.closest('.nex-feed-video-player').classList.add('needs-gesture'); });
                    }
                } else {
                    video.dataset.inViewport = 'false';
                    video.dataset.manuallyPaused = 'false';
                    video.pause();
                }
            });
        }, { threshold: [0, 0.3, 0.6, 1] });

        feedVideos.forEach(function (video) {
            video.dataset.manuallyPaused = 'false';
            feedVideoObserver.observe(video);
        });
    }

    const myDayInput = document.querySelector('[data-myday-media]');
    const myDayPreview = document.querySelector('[data-myday-preview]');
    if (myDayInput && myDayPreview) {
        let previewUrl = null;
        myDayInput.addEventListener('change', function () {
            myDayPreview.replaceChildren();
            myDayInput.setCustomValidity('');
            const file = myDayInput.files && myDayInput.files[0];
            if (!file) {
                myDayPreview.hidden = true;
                return;
            }

            if (previewUrl) URL.revokeObjectURL(previewUrl);
            previewUrl = URL.createObjectURL(file);
            myDayPreview.hidden = false;

            if (file.type.startsWith('video/')) {
                const video = document.createElement('video');
                video.src = previewUrl;
                video.controls = true;
                video.playsInline = true;
                video.preload = 'metadata';
                video.addEventListener('loadedmetadata', function () {
                    if (video.duration > 60) {
                        myDayInput.setCustomValidity('My Day videos can be up to 1 minute long.');
                        myDayPreview.dataset.error = 'Videos must be 1 minute or shorter.';
                    } else {
                        myDayPreview.dataset.error = '';
                    }
                });
                myDayPreview.appendChild(video);
            } else {
                const image = document.createElement('img');
                image.src = previewUrl;
                image.alt = 'My Day preview';
                myDayPreview.appendChild(image);
            }
        });
    }

});
