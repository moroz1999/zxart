window.jsColorLogics = new function() {
    var initComponents = function() {
        if (typeof jsColorPicker !== 'undefined') {
            var colors = jsColorPicker('input.jscolor', {
                customBG: '#222',
                readOnly: false,
                init: function(elm, colors) {
                    elm.style.backgroundColor = elm.value;
                    elm.style.color = colors.rgbaMixCustom.luminance > 0.22 ? '#222' : '#ddd';
                },
            });
        }
    };
    controller.addListener('initDom', initComponents);
};