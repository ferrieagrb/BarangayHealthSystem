document.addEventListener('DOMContentLoaded', function () {
    // Announcement slider: seamless infinite loop, with manual controls and a 5-second delay.
    const announcementSlider = document.getElementById('citizenAnnouncementSlider');
    const announcementTrack = document.getElementById('citizenAnnouncementTrack');
    const announcementOriginalSlides = announcementTrack
        ? Array.from(announcementTrack.querySelectorAll('.c-ann-slide'))
        : [];
    const announcementPrev = document.getElementById('citizenAnnouncementPrev');
    const announcementNext = document.getElementById('citizenAnnouncementNext');
    const announcementIndicator = document.getElementById('citizenAnnouncementIndicator');
    const announcementSlideDelay = 5000;
    const announcementTransitionMs = 650;
    let announcementAutoTimer = null;
    let announcementIndex = 0;
    let announcementPosition = 0;
    let announcementIsAnimating = false;
    let announcementRealSlides = announcementOriginalSlides.slice();

    function announcementMeasureStep() {
        if (!announcementTrack || !announcementRealSlides.length) return 0;
        const slideWidth = announcementRealSlides[0].getBoundingClientRect().width;
        const gap = parseFloat(getComputedStyle(announcementTrack).gap) || 0;
        return slideWidth + gap;
    }

    function announcementSetPosition(animate = true) {
        if (!announcementTrack || !announcementRealSlides.length) return;
        announcementTrack.style.transition = animate
            ? 'transform .65s cubic-bezier(.22,.61,.36,1)'
            : 'none';
        announcementTrack.style.transform =
            `translateX(-${announcementPosition * announcementMeasureStep()}px)`;
    }

    function announcementUpdateIndicator() {
        if (announcementIndicator && announcementRealSlides.length) {
            announcementIndicator.textContent =
                `Announcement ${announcementIndex + 1} of ${announcementRealSlides.length}`;
        }
        if (announcementPrev) announcementPrev.disabled = announcementRealSlides.length < 2;
        if (announcementNext) announcementNext.disabled = announcementRealSlides.length < 2;
    }

    function announcementMove(direction) {
        if (announcementRealSlides.length < 2 || announcementIsAnimating) return;
        announcementIsAnimating = true;
        announcementPosition += direction;
        announcementIndex = (announcementIndex + direction + announcementRealSlides.length)
            % announcementRealSlides.length;
        announcementSetPosition(true);
        announcementUpdateIndicator();
    }

    function stopAnnouncementAutoSlide() {
        if (announcementAutoTimer !== null) {
            window.clearTimeout(announcementAutoTimer);
            announcementAutoTimer = null;
        }
    }

    function startAnnouncementAutoSlide() {
        stopAnnouncementAutoSlide();
        if (announcementRealSlides.length < 2) return;
        announcementAutoTimer = window.setTimeout(function advanceAnnouncement() {
            announcementMove(1);
            // The next timeout is scheduled after the current slide transition completes.
        }, announcementSlideDelay);
    }

    if (announcementTrack && announcementOriginalSlides.length > 1) {
        // Add one clone at each end so both forward and backward wrapping are seamless.
        const firstClone = announcementOriginalSlides[0].cloneNode(true);
        const lastClone = announcementOriginalSlides[announcementOriginalSlides.length - 1].cloneNode(true);

        [firstClone, lastClone].forEach(function (clone) {
            clone.setAttribute('aria-hidden', 'true');
            clone.querySelectorAll('a, button, input, select, textarea, [tabindex]').forEach(function (el) {
                el.setAttribute('tabindex', '-1');
            });
        });

        announcementTrack.appendChild(firstClone);
        announcementTrack.insertBefore(lastClone, announcementTrack.firstChild);
        announcementPosition = 1;
        announcementSetPosition(false);
        announcementUpdateIndicator();

        // Force the initial no-transition position to be applied before enabling animation.
        requestAnimationFrame(function () {
            announcementTrack.style.transition = 'transform .65s cubic-bezier(.22,.61,.36,1)';
        });

        announcementTrack.addEventListener('transitionend', function (event) {
            if (event.propertyName !== 'transform') return;

            // If we land on either clone, silently reset to the matching real slide.
            if (announcementPosition === announcementRealSlides.length + 1) {
                announcementPosition = 1;
                announcementSetPosition(false);
            } else if (announcementPosition === 0) {
                announcementPosition = announcementRealSlides.length;
                announcementSetPosition(false);
            }

            announcementIsAnimating = false;
            startAnnouncementAutoSlide();
        });

        announcementPrev?.addEventListener('click', function () {
            stopAnnouncementAutoSlide();
            announcementMove(-1);
        });
        announcementNext?.addEventListener('click', function () {
            stopAnnouncementAutoSlide();
            announcementMove(1);
        });

        // Pause autoplay while hovered or focused; resume with a fresh 5-second wait.
        announcementSlider?.addEventListener('mouseenter', stopAnnouncementAutoSlide);
        announcementSlider?.addEventListener('mouseleave', startAnnouncementAutoSlide);
        announcementSlider?.addEventListener('focusin', stopAnnouncementAutoSlide);
        announcementSlider?.addEventListener('focusout', startAnnouncementAutoSlide);

        window.addEventListener('resize', function () {
            announcementSetPosition(false);
        });
        startAnnouncementAutoSlide();
    } else {
        announcementUpdateIndicator();
    }

    const nav = document.getElementById('citizenSectionNav');
    if (!nav) return;

    const navTrack = nav.querySelector('.c-header-section-track');
    const links = [...nav.querySelectorAll('a[href^="#"]')];

    const sections = links
        .map(link => document.getElementById(
            link.getAttribute('href').substring(1)
        ))
        .filter(Boolean);

    if (!sections.length) return;
    let pendingNavigationSection = null;
    let activeSection = null;
    let updateScheduled = false;
    let swipeStart = null;
    let suppressSwipeClick = false;
    let swipeClickTimer = null;

    function centerActiveLink() {
        if (!navTrack || !nav.clientWidth) return;

        const activeLink = links.find(link => link.classList.contains('active'));
        if (!activeLink) return;

        const offset = nav.clientWidth / 2
            - activeLink.offsetLeft
            - activeLink.offsetWidth / 2;
        navTrack.style.transform = `translate3d(${offset}px, 0, 0)`;
    }

    function setActive(section) {
        const changed = activeSection !== section;
        activeSection = section;

        links.forEach(link => {
            const active =
                link.getAttribute('href') === '#' + section.id;

            link.classList.toggle('active', active);

            if (active) {
                link.setAttribute('aria-current', 'location');
            } else {
                link.removeAttribute('aria-current');
            }
        });

        if (changed) centerActiveLink();
    }

    function getSectionFromHash() {
        if (!window.location.hash) return null;

        const targetId = decodeURIComponent(window.location.hash.slice(1));
        return sections.find(section => section.id === targetId) || null;
    }

    function updateActiveTab() {
        const header = document.querySelector('.top-bar');
        const headerHeight = header ? header.offsetHeight : 0;
        const activationPoint = headerHeight + 25;
        const hashSection = getSectionFromHash();
        let current = sections[0];

        sections.forEach(section => {
            if (section.getBoundingClientRect().top <= activationPoint) {
                current = section;
            }
        });

        if (pendingNavigationSection) {
            const bounds = pendingNavigationSection.getBoundingClientRect();
            if (bounds.top <= activationPoint + 1 && bounds.bottom > activationPoint) {
                current = pendingNavigationSection;
                pendingNavigationSection = null;
            } else {
                current = pendingNavigationSection;
            }
        } else if (hashSection) {
            const bounds = hashSection.getBoundingClientRect();
            if (bounds.top <= activationPoint + 1 && bounds.bottom > activationPoint) {
                current = hashSection;
            }
        }

        const lastSection = sections[sections.length - 1];
        if (lastSection) {
            const lastBounds = lastSection.getBoundingClientRect();
            const isAtPageBottom = window.scrollY + window.innerHeight
                >= document.documentElement.scrollHeight - 2;
            const lastSectionVisible = lastBounds.top < window.innerHeight
                && lastBounds.bottom > headerHeight;

            if (!pendingNavigationSection && isAtPageBottom && lastSectionVisible) {
                current = lastSection;
            }
        }

        setActive(current);
    }

    function scheduleActiveTabUpdate() {
        if (updateScheduled) return;
        updateScheduled = true;
        window.requestAnimationFrame(function () {
            updateScheduled = false;
            updateActiveTab();
        });
    }

    function navigateToSection(target) {
        pendingNavigationSection = target;
        const targetHash = '#' + target.id;
        if (window.location.hash !== targetHash) {
            window.history.pushState(null, '', targetHash);
        }
        setActive(target);

        const header = document.querySelector('.top-bar');
        const headerHeight = header ? header.offsetHeight : 0;
        const targetY =
            target.getBoundingClientRect().top +
            window.scrollY -
            headerHeight -
            12;

        window.scrollTo({
            top: Math.max(0, targetY),
            behavior: 'smooth'
        });
    }

    links.forEach(link => {
        link.addEventListener('click', function (event) {
            if (suppressSwipeClick && event.detail !== 0) {
                event.preventDefault();
                event.stopPropagation();
                suppressSwipeClick = false;
                window.clearTimeout(swipeClickTimer);
                return;
            }

            const target = document.getElementById(
                this.getAttribute('href').substring(1)
            );

            if (!target) return;

            event.preventDefault();
            navigateToSection(target);
        });
    });

    nav.addEventListener('pointerdown', function (event) {
        if (!event.isPrimary || (event.pointerType === 'mouse' && event.button !== 0)) return;
        swipeStart = { x: event.clientX, y: event.clientY };
    });

    nav.addEventListener('pointerup', function (event) {
        if (!swipeStart) return;

        const deltaX = event.clientX - swipeStart.x;
        const deltaY = event.clientY - swipeStart.y;
        swipeStart = null;

        if (Math.abs(deltaX) < 40 || Math.abs(deltaX) <= Math.abs(deltaY)) return;

        const activeIndex = links.findIndex(link => link.classList.contains('active'));
        const nextIndex = Math.max(0, Math.min(
            links.length - 1,
            activeIndex + (deltaX < 0 ? 1 : -1)
        ));
        if (nextIndex === activeIndex) return;

        suppressSwipeClick = true;
        window.clearTimeout(swipeClickTimer);
        swipeClickTimer = window.setTimeout(function () {
            suppressSwipeClick = false;
        }, 500);
        navigateToSection(sections[nextIndex]);
    });

    nav.addEventListener('pointercancel', function () {
        swipeStart = null;
    });

    nav.addEventListener('keydown', function (event) {
        if (event.key !== 'ArrowLeft' && event.key !== 'ArrowRight') return;

        const activeIndex = links.findIndex(link => link.classList.contains('active'));
        const nextIndex = Math.max(0, Math.min(
            links.length - 1,
            activeIndex + (event.key === 'ArrowRight' ? 1 : -1)
        ));
        if (nextIndex === activeIndex) return;

        event.preventDefault();
        links[nextIndex].focus();
        navigateToSection(sections[nextIndex]);
    });

    window.addEventListener('scroll', scheduleActiveTabUpdate, {
        passive: true
    });

    window.addEventListener('resize', function () {
        centerActiveLink();
        scheduleActiveTabUpdate();
    });

    function cancelPendingNavigation() {
        if (!pendingNavigationSection) return;
        pendingNavigationSection = null;
        scheduleActiveTabUpdate();
    }

    window.addEventListener('wheel', cancelPendingNavigation, { passive: true });
    window.addEventListener('touchstart', cancelPendingNavigation, { passive: true });
    window.addEventListener('pointerdown', function (event) {
        if (!nav.contains(event.target)) cancelPendingNavigation();
    });

    if ('ResizeObserver' in window) {
        const sectionResizeObserver = new ResizeObserver(scheduleActiveTabUpdate);
        sections.forEach(section => sectionResizeObserver.observe(section));
    }

    function restoreSectionFromHistory() {
        const target = getSectionFromHash();
        if (target) {
            pendingNavigationSection = target;
            const header = document.querySelector('.top-bar');
            const headerHeight = header ? header.offsetHeight : 0;
            const targetY = target.getBoundingClientRect().top + window.scrollY - headerHeight - 12;
            window.scrollTo({ top: Math.max(0, targetY), behavior: 'auto' });
        } else {
            pendingNavigationSection = null;
        }
        updateActiveTab();
    }

    window.addEventListener('hashchange', restoreSectionFromHistory);
    window.addEventListener('popstate', restoreSectionFromHistory);
    window.addEventListener('pageshow', scheduleActiveTabUpdate);
    window.addEventListener('load', scheduleActiveTabUpdate);

    updateActiveTab();
    window.requestAnimationFrame(updateActiveTab);
    centerActiveLink();
    window.requestAnimationFrame(function () {
        nav.classList.add('is-ready');
        centerActiveLink();
    });
    if (getSectionFromHash()) {
        window.requestAnimationFrame(restoreSectionFromHistory);
    }
});
