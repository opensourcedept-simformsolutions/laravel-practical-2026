const SIDEBAR_STORAGE_KEY = 'sidebar-collapsed';

function applySidebarState($sidebarContainer, $toggleButton, isCollapsed) {
    $sidebarContainer.toggleClass('collapsed', isCollapsed);
    $toggleButton.attr('aria-expanded', String(!isCollapsed));
}

$(function () {
   
    const $sidebarContainer = $('#sidebarContainer');
    const $toggleButton = $('#sidebarToggle');

    if (!$sidebarContainer.length || !$toggleButton.length) {
        return;
    }

    function syncSidebarState() {
        if (window.innerWidth < 992) {
            // Always collapsed on mobile
            applySidebarState($sidebarContainer, $toggleButton, true);
        } else {
            const isCollapsed =
                localStorage.getItem(SIDEBAR_STORAGE_KEY) === 'true';

            applySidebarState($sidebarContainer, $toggleButton, isCollapsed);
        }
    }

    syncSidebarState();

    $(window).on('resize', syncSidebarState);

    $toggleButton.on('click', function () {
        const nextState = !$sidebarContainer.hasClass('collapsed');

        applySidebarState($sidebarContainer, $toggleButton, nextState);

        if (window.innerWidth >= 992) {
            localStorage.setItem(
                SIDEBAR_STORAGE_KEY,
                String(nextState)
            );
        }
    });
});

$(function () {
    $('#reportsToggle').click(function () {
        $('#reportsMenu').toggleClass('d-none');
        $('#reportsArrow').toggleClass('rotate-180');
    });
});
