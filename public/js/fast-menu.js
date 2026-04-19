(function () {
    function moveHeaderLeft() {
        const headerLeft = document.querySelector('[data-entomai-header-left]')
        const brand = document.querySelector('header.navbar .navbar-brand')
        const hasContent = headerLeft?.children.length > 0

        if (!headerLeft || !brand || headerLeft.dataset.moved === '1') {
            return
        }

        if (!hasContent) {
            return
        }

        headerLeft.dataset.moved = '1'
        headerLeft.classList.remove('d-none')
        headerLeft.classList.add('d-flex', 'align-items-center', 'gap-2', 'me-3')

        if (headerLeft.dataset.entomaiCompactBrand === '1') {
            brand.closest('header.navbar')?.classList.add('entomai-header-left-navbar')
        }

        brand.insertAdjacentElement('afterend', headerLeft)
    }

    function setupStickyAdminShell() {
        const headerLeft = document.querySelector('[data-entomai-header-left]')
        const header = document.querySelector('#app > header.navbar')
        const sidebar = document.querySelector('#sidebar-menu-main')

        if (!headerLeft || headerLeft.dataset.entomaiStickyShell !== '1' || !header || !sidebar) {
            return
        }

        document.body.classList.add('entomai-admin-sticky-shell')
    }

    function bindSubMenus() {
        document.querySelectorAll('[data-entomai-fast-menu-submenu]').forEach((toggle) => {
            if (toggle.dataset.bound === '1') {
                return
            }

            toggle.dataset.bound = '1'
            toggle.addEventListener('click', function (event) {
                event.preventDefault()
                event.stopPropagation()

                const submenu = toggle.nextElementSibling

                if (!submenu) {
                    return
                }

                const parentMenu = toggle.closest('.dropdown-menu')

                parentMenu?.querySelectorAll(':scope > .dropend > .dropdown-menu.show').forEach((menu) => {
                    if (menu !== submenu) {
                        menu.classList.remove('show')
                        menu.previousElementSibling?.setAttribute('aria-expanded', 'false')
                    }
                })

                submenu.classList.toggle('show')
                toggle.setAttribute('aria-expanded', submenu.classList.contains('show') ? 'true' : 'false')
            })
        })
    }

    function hideViewWebsiteButton() {
        const headerLeft = document.querySelector('[data-entomai-header-left]')
        const header = document.querySelector('header.navbar')

        if (!headerLeft || headerLeft.dataset.entomaiHideViewWebsite !== '1' || !header) {
            return
        }

        header.querySelectorAll('a[href][target="_blank"]').forEach((link) => {
            const text = link.textContent.trim().toLowerCase()

            if (!link.querySelector('.icon-tabler-world') && !text.includes('view website')) {
                return
            }

            const wrapper = link.closest('.nav-item') || link.closest('.d-flex.align-items-center') || link

            wrapper.classList.add('d-none')
        })
    }

    function closeSubMenus(event) {
        if (event.target.closest('[data-entomai-header-left]')) {
            return
        }

        document.querySelectorAll('[data-entomai-header-left] .dropdown-menu.show').forEach((menu) => {
            menu.classList.remove('show')
            menu.previousElementSibling?.setAttribute('aria-expanded', 'false')
        })
    }

    function hideLegacyPluginNotifications() {
        const header = document.querySelector('header.navbar')

        if (!header) {
            return
        }

        const notifications = [
            {
                current: '[data-entomai-notification="ecommerce"]',
                legacyIcon: '.icon-tabler-shopping-cart',
            },
            {
                current: '[data-entomai-notification="contact"]',
                legacyIcon: '.icon-tabler-mail',
            },
        ]

        notifications.forEach((notification) => {
            if (!document.querySelector(notification.current)) {
                return
            }

            header.querySelectorAll('.nav-item.dropdown').forEach((item) => {
                if (item.closest('[data-entomai-header-left]') || !item.querySelector(notification.legacyIcon)) {
                    return
                }

                item.classList.add('d-none')
            })
        })
    }

    function boot() {
        setupStickyAdminShell()
        moveHeaderLeft()
        bindSubMenus()
        hideLegacyPluginNotifications()
        hideViewWebsiteButton()
        document.addEventListener('click', closeSubMenus)
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot)
    } else {
        boot()
    }
})()
