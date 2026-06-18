const SIDEBAR_STORAGE_KEY = 'sidebar-collapsed';

function applySidebarState($sidebarContainer, $toggleButton, isCollapsed) {
    $sidebarContainer.toggleClass('collapsed', isCollapsed);
    $toggleButton.attr('aria-expanded', String(!isCollapsed));
}

window.$(function () {
    const $sidebarContainer = window.$('#sidebarContainer');
    const $toggleButton = window.$('#sidebarToggle');

    if (!$sidebarContainer.length || !$toggleButton.length) {
        return;
    }

    const isCollapsed = window.localStorage.getItem(SIDEBAR_STORAGE_KEY) === 'true';

    applySidebarState($sidebarContainer, $toggleButton, isCollapsed);

    $toggleButton.on('click', function () {
        const nextState = !$sidebarContainer.hasClass('collapsed');

        applySidebarState($sidebarContainer, $toggleButton, nextState);
        window.localStorage.setItem(SIDEBAR_STORAGE_KEY, String(nextState));
    });
});
