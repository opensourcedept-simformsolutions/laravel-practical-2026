const SIDEBAR_STORAGE_KEY = 'sidebar-collapsed';

function applySidebarState($sidebarContainer, $toggleButton, isCollapsed) {
    $sidebarContainer.toggleClass('collapsed', isCollapsed);
    $toggleButton.attr('aria-expanded', String(!isCollapsed));

    const $backdrop = $('#sidebarBackdrop');
    if ($backdrop.length) {
        if (window.innerWidth < 992 && !isCollapsed) {
            $backdrop.addClass('show');
            $('body').css('overflow', 'hidden');
        } else {
            $backdrop.removeClass('show');
            $('body').css('overflow', '');
        }
    }

    if (isCollapsed) {
        $('.sidebar-link').each(function () {
            const $link = $(this);
            const $label = $link.find('.sidebar-link-label');
            if ($label.length) {
                const $span = $label.find('span:first-child');
                const titleText = $span.length ? $span.text().trim() : $label.text().trim();
                if (titleText) {
                    $link.attr('title', titleText);
                }
            }
        });

        const $reportsToggle = $('#reportsToggle');
        if ($reportsToggle.length) {
            const $label = $reportsToggle.find('.sidebar-link-label');
            if ($label.length) {
                const titleText = $label.text().trim();
                if (titleText) {
                    $reportsToggle.attr('title', titleText);
                }
            }
        }
    } else {
        $('.sidebar-link').removeAttr('title');
        $('#reportsToggle').removeAttr('title');
    }
}

function getSidebarCollapsed() {
    const value = `; ${document.cookie}`;
    const parts = value.split(`; sidebar-collapsed=`);
    if (parts.length === 2) {
        return parts.pop().split(';').shift() === 'true';
    }
    return localStorage.getItem(SIDEBAR_STORAGE_KEY) === 'true';
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
        if (window.innerWidth === lastWidth) {
            return;
        }
        lastWidth = window.innerWidth;

        if (window.innerWidth < 992) {
            applySidebarState($sidebarContainer, $toggleButton, true);
        } else {
            const isCollapsed = getSidebarCollapsed();
            applySidebarState($sidebarContainer, $toggleButton, isCollapsed);
        }
    }

    if (window.innerWidth < 992) {
        applySidebarState($sidebarContainer, $toggleButton, true);
    } else {
        const isCollapsed = getSidebarCollapsed();
        applySidebarState($sidebarContainer, $toggleButton, isCollapsed);
    }

    $(window).on('resize', syncSidebarState);

    $toggleButton.on('click', function (e) {
        e.stopPropagation();
        const nextState = !$sidebarContainer.hasClass('collapsed');
        applySidebarState($sidebarContainer, $toggleButton, nextState);

        if (window.innerWidth >= 992) {
            localStorage.setItem(SIDEBAR_STORAGE_KEY, String(nextState));
            document.cookie = "sidebar-collapsed=" + nextState + "; path=/; max-age=" + (30 * 24 * 60 * 60);
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
        const $menu = $('#reportsMenu');
        const $arrow = $('#reportsArrow');
        
        $menu.slideToggle(200);
        $arrow.toggleClass('rotate-180');
    });

    // Persist sidebar scroll position
    const $sidebarMenu = $('.sidebar-menu');
    if ($sidebarMenu.length) {
        const savedScrollTop = sessionStorage.getItem('sidebar-scroll');
        if (savedScrollTop !== null) {
            $sidebarMenu.scrollTop(parseInt(savedScrollTop, 10));
        }

        $sidebarMenu.on('scroll', function() {
            sessionStorage.setItem('sidebar-scroll', $sidebarMenu.scrollTop());
        });

        if (savedScrollTop === null) {
            const $activeLink = $sidebarMenu.find('.active');
            if ($activeLink.length) {
                $activeLink[0].scrollIntoView({ block: 'nearest' });
            }
        }
    }
});
