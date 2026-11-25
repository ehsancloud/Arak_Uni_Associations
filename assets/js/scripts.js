/**
 * سامانه انجمن‌های علمی دانشگاه اراک - اسکریپت‌های تعاملی و انیمیشن‌ها
 */
document.addEventListener('DOMContentLoaded', () => {

    // ---------- ۱. کنترلر نوار متحرک اخبار (News Ticker) ----------
    const tickerTrack = document.getElementById('tickerTrack');
    const tickerToggleBtn = document.getElementById('tickerToggleBtn');
    
    if (tickerTrack) {
        // برای لوپ بی‌نهایت یکپارچه و بدون پرش، محتوا را تکرار می‌کنیم
        const rawContent = tickerTrack.innerHTML;
        tickerTrack.innerHTML = rawContent + '<span class="ticker-sep">|</span>' + rawContent;

        if (tickerToggleBtn) {
            const iconPause = tickerToggleBtn.querySelector('.icon-pause');
            const iconPlay = tickerToggleBtn.querySelector('.icon-play');

            tickerToggleBtn.addEventListener('click', () => {
                const isPaused = tickerTrack.classList.toggle('paused');
                if (iconPause && iconPlay) {
                    if (isPaused) {
                        iconPause.style.display = 'none';
                        iconPlay.style.display = 'inline-block';
                        tickerToggleBtn.setAttribute('title', 'پخش اخبار');
                    } else {
                        iconPause.style.display = 'inline-block';
                        iconPlay.style.display = 'none';
                        tickerToggleBtn.setAttribute('title', 'توقف اخبار');
                    }
                }
            });
        }
    }

    // ---------- ۲. منوی کشویی واکنش‌گرا در موبایل (Drawer) ----------
    const mobileMenuBtn = document.getElementById('mobileMenuBtn');
    const drawerCloseBtn = document.getElementById('drawerCloseBtn');
    const mobileDrawer = document.getElementById('mobileDrawer');
    const mobileDrawerOverlay = document.getElementById('mobileDrawerOverlay');

    function openMobileMenu() {
        if (mobileDrawer && mobileDrawerOverlay) {
            mobileDrawer.classList.add('active');
            mobileDrawerOverlay.classList.add('active');
            document.body.style.overflow = 'hidden';
        }
    }

    function closeMobileMenu() {
        if (mobileDrawer && mobileDrawerOverlay) {
            mobileDrawer.classList.remove('active');
            mobileDrawerOverlay.classList.remove('active');
            document.body.style.overflow = '';
        }
    }

    if (mobileMenuBtn) {
        mobileMenuBtn.addEventListener('click', openMobileMenu);
    }
    if (drawerCloseBtn) {
        drawerCloseBtn.addEventListener('click', closeMobileMenu);
    }
    if (mobileDrawerOverlay) {
        mobileDrawerOverlay.addEventListener('click', closeMobileMenu);
    }

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            closeMobileMenu();
        }
    });

    // ---------- ۳. افکت هدر هنگام اسکرول ----------
    const mainHeader = document.getElementById('mainHeader');
    if (mainHeader) {
        window.addEventListener('scroll', () => {
            if (window.scrollY > 20) {
                mainHeader.style.boxShadow = '0 8px 24px rgba(14, 31, 61, 0.08)';
            } else {
                mainHeader.style.boxShadow = '0 4px 20px rgba(14, 31, 61, 0.04)';
            }
        }, { passive: true });
    }

    // ---------- ۴. اسکلتون لودینگ (Skeleton Screen Loader) ----------
    const skeleton = document.getElementById('pageSkeleton');
    if (skeleton) {
        const dismissSkeleton = () => {
            if (!skeleton.classList.contains('hidden')) {
                skeleton.classList.add('hidden');
                setTimeout(() => {
                    if (skeleton.parentNode) {
                        skeleton.parentNode.removeChild(skeleton);
                    }
                }, 350);
            }
        };

        if (document.readyState === 'complete' || document.readyState === 'interactive') {
            dismissSkeleton();
        } else {
            window.addEventListener('load', dismissSkeleton);
        }
        setTimeout(dismissSkeleton, 400);
    }

});
