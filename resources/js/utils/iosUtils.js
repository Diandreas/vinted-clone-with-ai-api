/**
 * iOS Utilities for RIKEAA PWA
 * Handles iOS-specific functionality and optimizations
 */

// Detect iOS device
export const isIOS = () => {
    return /iPad|iPhone|iPod/.test(navigator.userAgent) ||
        (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1);
};

// Detect iOS version
export const getIOSVersion = () => {
    if (!isIOS()) return null;

    const match = navigator.userAgent.match(/OS (\d+)_(\d+)_?(\d+)?/);
    if (match) {
        return {
            major: parseInt(match[1]),
            minor: parseInt(match[2]),
            patch: match[3] ? parseInt(match[3]) : 0
        };
    }
    return null;
};

// Check if device supports specific iOS features
export const supportsIOSFeature = (feature) => {
    const version = getIOSVersion();
    if (!version) return false;

    const featureSupport = {
        'safe-area': version.major >= 11,
        'backdrop-filter': version.major >= 12,
        'web-share': version.major >= 12,
        'web-push': version.major >= 16,
        'service-worker': version.major >= 11,
        'pwa-install': version.major >= 11
    };

    return featureSupport[feature] || false;
};

// Add iOS-specific classes to body
export const addIOSClasses = () => {
    if (isIOS()) {
        document.body.classList.add('ios-device');

        const version = getIOSVersion();
        if (version) {
            document.body.classList.add(`ios-${version.major}`);
            if (version.major >= 11) {
                document.body.classList.add('ios-safe-area');
            }
            if (version.major >= 12) {
                document.body.classList.add('ios-modern');
            }
        }

        // Add orientation class
        const addOrientationClass = () => {
            document.body.classList.remove('ios-landscape', 'ios-portrait');
            if (window.innerHeight < window.innerWidth) {
                document.body.classList.add('ios-landscape');
            } else {
                document.body.classList.add('ios-portrait');
            }
        };

        addOrientationClass();
        window.addEventListener('orientationchange', addOrientationClass);
        window.addEventListener('resize', addOrientationClass);
    }
};

// Handle iOS viewport
export const setupIOSViewport = () => {
    if (isIOS()) {
        const viewport = document.querySelector('meta[name=viewport]');
        if (viewport) {
            viewport.setAttribute('content', 'width=device-width, initial-scale=1, viewport-fit=cover, user-scalable=no');
        }
    }
};

// iOS-specific touch handling
export const setupIOSTouchHandling = () => {
    if (isIOS()) {
        // Prevent double-tap zoom
        let lastTouchEnd = 0;
        document.addEventListener('touchend', (event) => {
            const now = (new Date()).getTime();
            if (now - lastTouchEnd <= 300) {
                event.preventDefault();
            }
            lastTouchEnd = now;
        }, false);

        // Prevent pull-to-refresh on body
        document.body.addEventListener('touchmove', (event) => {
            if (event.target.closest('.scrollable-content')) {
                return;
            }
            event.preventDefault();
        }, { passive: false });
    }
};

// iOS-specific scroll handling
export const setupIOSScrollHandling = () => {
    if (isIOS()) {
        // Smooth scrolling for iOS
        const style = document.createElement('style');
        style.textContent = `
      .ios-device {
        -webkit-overflow-scrolling: touch;
        scroll-behavior: smooth;
      }
    `;
        document.head.appendChild(style);
    }
};

// Handle iOS keyboard appearance
export const setupIOSKeyboardHandling = () => {
    if (isIOS()) {
        const inputs = document.querySelectorAll('input, textarea, select');

        inputs.forEach(input => {
            input.addEventListener('focus', () => {
                // Add class to body when keyboard is shown
                document.body.classList.add('ios-keyboard-visible');

                // Scroll input into view
                setTimeout(() => {
                    input.scrollIntoView({
                        behavior: 'smooth',
                        block: 'center'
                    });
                }, 300);
            });

            input.addEventListener('blur', () => {
                document.body.classList.remove('ios-keyboard-visible');
            });
        });
    }
};

// iOS-specific PWA handling
export const setupIOSPWA = () => {
    if (isIOS()) {
        // Check if app is installed
        const isStandalone = window.navigator.standalone === true;

        if (isStandalone) {
            document.body.classList.add('ios-pwa-installed');

            // Hide browser UI elements
            const style = document.createElement('style');
            style.textContent = `
        .ios-pwa-installed .browser-only {
          display: none !important;
        }
      `;
            document.head.appendChild(style);
        }

        // Handle PWA installation
        if (!isStandalone) {
            // Show iOS-specific install instructions
            const showIOSInstallPrompt = () => {
                const prompt = document.createElement('div');
                prompt.className = 'ios-install-prompt fixed bottom-0 left-0 right-0 z-50 bg-white border-t border-gray-200 p-4';
                prompt.innerHTML = `
          <div class="text-center">
            <h3 class="text-lg font-semibold text-gray-900 mb-2">Installer RIKEAA</h3>
            <p class="text-sm text-gray-600 mb-4">Appuyez sur Partager puis "Sur l'écran d'accueil"</p>
            <button onclick="this.parentElement.parentElement.remove()" class="text-primary-600 font-medium">
              Compris
            </button>
          </div>
        `;
                document.body.appendChild(prompt);
            };

            // Show prompt after delay
            setTimeout(showIOSInstallPrompt, 5000);
        }
    }
};

// Initialize all iOS utilities
export const initIOSUtils = () => {
    addIOSClasses();
    setupIOSViewport();
    setupIOSTouchHandling();
    setupIOSScrollHandling();
    setupIOSKeyboardHandling();
    setupIOSPWA();
};

// Export default function
export default {
    isIOS,
    getIOSVersion,
    supportsIOSFeature,
    addIOSClasses,
    setupIOSViewport,
    setupIOSTouchHandling,
    setupIOSScrollHandling,
    setupIOSKeyboardHandling,
    setupIOSPWA,
    initIOSUtils
};
