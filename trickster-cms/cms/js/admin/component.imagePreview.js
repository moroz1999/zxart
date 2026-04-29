window.imagePreviewComponent = function(imgContainer) {
    var img = imgContainer.querySelector('img');
    var imgPreviewContainer = '';
    var imgPreviewImg = '';
    var init = function() {
        if (img) {
            imgPreviewContainer = document.createElement('div');
            imgPreviewContainer.className = 'image_preview_container';

            imgPreviewImg = document.createElement('img');
            imgPreviewImg.src = img.src;
            imgPreviewImg.alt = ' ';
            if (imgPreviewImg.src != '') {
                imgPreviewContainer.appendChild(imgPreviewImg);
                imgContainer.appendChild(imgPreviewContainer);

                eventsManager.addHandler(img, 'mouseenter', mouseEnterEvent);
                eventsManager.addHandler(img, 'mouseleave', mouseLeaveEvent);
            }
        }
    };
    var mouseEnterEvent = function() {
        imgPreviewContainer.style.visibility = 'visible';
    };

    var mouseLeaveEvent = function() {
        imgPreviewContainer.style.visibility = 'hidden';
    };

    init();
};
