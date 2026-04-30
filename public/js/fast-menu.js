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

    function bindCacheCleaner() {
        if (document.documentElement.dataset.entomaiCacheCleanerBound === '1') {
            return
        }

        document.documentElement.dataset.entomaiCacheCleanerBound = '1'

        document.addEventListener('click', function (event) {
            const button = event.target.closest('[data-entomai-cache-action]')

            if (!button) {
                return
            }

            event.preventDefault()
            event.stopPropagation()

            if (button.disabled) {
                return
            }

            const url = button.dataset.url
            const type = button.dataset.type

            if (!url || !type) {
                return
            }

            const request = makeCacheRequest(url, type)

            if (!request) {
                if (typeof Botble !== 'undefined' && Botble.showError) {
                    Botble.showError('The cache request client is not available.')
                }

                return
            }

            const buttonElement = window.jQuery ? window.jQuery(button) : button

            if (typeof Botble !== 'undefined' && Botble.showButtonLoading && window.jQuery) {
                Botble.showButtonLoading(buttonElement)
            }

            button.disabled = true

            request
                .then((response) => {
                    const data = response.data || response || {}
                    const message = data.message
                    const formattedCacheSize = data.data?.formatted_cache_size

                    if (message && typeof Botble !== 'undefined' && Botble.showSuccess) {
                        Botble.showSuccess(message)
                    }

                    if (formattedCacheSize) {
                        document.querySelectorAll('[data-entomai-cache-size]').forEach((element) => {
                            element.textContent = formattedCacheSize
                        })
                    }
                })
                .catch((error) => {
                    const message =
                        error?.responseJSON?.message ||
                        error?.response?.data?.message ||
                        error?.message ||
                        'The cache could not be cleared.'

                    if (typeof Botble !== 'undefined' && Botble.showError) {
                        Botble.showError(message)
                    }
                })
                .finally(() => {
                    if (typeof Botble !== 'undefined' && Botble.hideButtonLoading && window.jQuery) {
                        Botble.hideButtonLoading(buttonElement)
                    }

                    button.disabled = false
                })
        })
    }

    function makeCacheRequest(url, type) {
        if (typeof $httpClient !== 'undefined') {
            return $httpClient.make().post(url, { type })
        }

        if (window.jQuery) {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')

            return new Promise((resolve, reject) => {
                window.jQuery
                    .ajax({
                        method: 'POST',
                        url,
                        data: { type },
                        headers: csrfToken ? { 'X-CSRF-TOKEN': csrfToken } : {},
                    })
                    .done(resolve)
                    .fail(reject)
            })
        }

        return null
    }

    function boot() {
        setupStickyAdminShell()
        moveHeaderLeft()
        bindSubMenus()
        bindCacheCleaner()
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
