/**
 * FAQ accordion — progressive enhancement
 * Answers are rendered in DOM so search engines / LLMs can read them.
 * JS just adds the slide animation and aria-expanded state.
 */
function initFaq($) {
    $(document).on('click', '.faq-trigger', function () {
        var $item  = $(this).closest('.faq-item');
        var isOpen = $item.hasClass('is-open');

        // Close all others
        $('.faq-item').not($item).removeClass('is-open')
            .find('.faq-content').stop(true, true).slideUp(250);
        $('.faq-trigger').not(this).attr('aria-expanded', 'false');

        // Toggle current
        if (isOpen) {
            $item.removeClass('is-open');
            $item.find('.faq-content').stop(true, true).slideUp(250);
            $(this).attr('aria-expanded', 'false');
        } else {
            $item.addClass('is-open');
            $item.find('.faq-content').stop(true, true).slideDown(250);
            $(this).attr('aria-expanded', 'true');
        }
    });
}

module.exports = { initFaq: initFaq };
