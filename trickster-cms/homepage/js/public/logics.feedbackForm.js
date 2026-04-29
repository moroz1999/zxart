window.feedbackLogics = new function() {
    var init = function() {
        var elements = document.querySelectorAll('.feedback_form');
        for (var i = 0; i < elements.length; i++) {
            new FeedbackFormComponent(elements[i]);
        }
    };
    controller.addListener('initDom', init);
};