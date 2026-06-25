const SIDEBAR_STORAGE_KEY = 'sidebar-collapsed';

function applySidebarState($sidebarContainer, $toggleButton, isCollapsed) {
    $sidebarContainer.toggleClass('collapsed', isCollapsed);
    $toggleButton.attr('aria-expanded', String(!isCollapsed));

    // Handle backdrop visibility on mobile
    const $backdrop = $('#sidebarBackdrop');
    if ($backdrop.length) {
        if (window.innerWidth < 992 && !isCollapsed) {
            $backdrop.addClass('show');
            $('body').css('overflow', 'hidden'); // Prevent scrolling body when mobile menu is open
        } else {
            $backdrop.removeClass('show');
            $('body').css('overflow', '');
        }
    }
}

$(function () {
    const $sidebarContainer = $('#sidebarContainer');
    const $toggleButton = $('#sidebarToggle');
    const $backdrop = $('#sidebarBackdrop');
    const $closeButton = $('#sidebarClose');

    if (!$sidebarContainer.length || !$toggleButton.length) {
        return;
    }

    let lastWidth = window.innerWidth;

    function syncSidebarState() {
        // Only trigger layout sync if actual viewport width changes (ignore mobile virtual keyboard/scroll resize)
        if (window.innerWidth === lastWidth) {
            return;
        }
        lastWidth = window.innerWidth;

        if (window.innerWidth < 992) {
            // Collapse by default on mobile viewports
            applySidebarState($sidebarContainer, $toggleButton, true);
        } else {
            const isCollapsed = localStorage.getItem(SIDEBAR_STORAGE_KEY) === 'true';
            applySidebarState($sidebarContainer, $toggleButton, isCollapsed);
        }
    }

    // Initial load sync
    if (window.innerWidth < 992) {
        applySidebarState($sidebarContainer, $toggleButton, true);
    } else {
        const isCollapsed = localStorage.getItem(SIDEBAR_STORAGE_KEY) === 'true';
        applySidebarState($sidebarContainer, $toggleButton, isCollapsed);
    }

    $(window).on('resize', syncSidebarState);

    $toggleButton.on('click', function (e) {
        e.stopPropagation();
        const nextState = !$sidebarContainer.hasClass('collapsed');
        applySidebarState($sidebarContainer, $toggleButton, nextState);

        if (window.innerWidth >= 992) {
            localStorage.setItem(SIDEBAR_STORAGE_KEY, String(nextState));
        }
    });

    if ($closeButton.length) {
        $closeButton.on('click', function () {
            applySidebarState($sidebarContainer, $toggleButton, true);
        });
    }

    if ($backdrop.length) {
        $backdrop.on('click', function () {
            applySidebarState($sidebarContainer, $toggleButton, true);
        });
    }
});

$(function () {
    $('#reportsToggle').click(function () {
        $('#reportsMenu').toggleClass('d-none');
        $('#reportsArrow').toggleClass('rotate-180');
    });
});
