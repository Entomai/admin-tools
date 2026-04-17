(function () {
    function moveHeaderLeft() {
        const headerLeft = document.querySelector('[data-entomai-header-left]')
        const brand = document.querySelector('header.navbar .navbar-brand')

        if (!headerLeft || !brand || headerLeft.dataset.moved === '1') {
            return
        }

        headerLeft.dataset.moved = '1'
        headerLeft.classList.remove('d-none')
        headerLeft.classList.add('d-flex', 'align-items-center', 'gap-2', 'me-3')

        brand.closest('header.navbar')?.classList.add('entomai-header-left-navbar')
        brand.insertAdjacentElement('afterend', headerLeft)
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
        moveHeaderLeft()
        bindSubMenus()
        hideLegacyPluginNotifications()
        document.addEventListener('click', closeSubMenus)
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot)
    } else {
        boot()
    }
})()
