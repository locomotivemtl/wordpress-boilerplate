import { $scroll } from '@stores/scroll';
import { isAnimationsReduced } from '@scripts/stores/animations';

import LocomotiveScroll, {
    type lenisTargetScrollTo,
    type ILenisScrollToOptions
} from 'locomotive-scroll';

export class Scroll {
    static locomotiveScroll: LocomotiveScroll | null = null;
    static isInitialized: boolean = false;
    static isSmooth: boolean = !isAnimationsReduced.get();

    // =============================================================================
    // Lifecycle
    // =============================================================================
    static init() {
        // Handle animation stop/active
        isAnimationsReduced.subscribe((isReduced) => {
            this.isSmooth = !isReduced;

            if (!this.isInitialized) {
                this.initScroll();
            } else {
                this.destroy();
                requestAnimationFrame(() => {
                    this.initScroll();
                });
            }
        });
    }

    static initScroll() {
        this.locomotiveScroll = new LocomotiveScroll({
            lenisOptions: {
                smoothWheel: this.isSmooth
            },
            scrollCallback({ scroll, limit, velocity, direction, progress }) {
                $scroll.set({
                    scroll,
                    limit,
                    velocity,
                    direction,
                    progress
                });
            }
        });

        // Prevent scroll on CookieYes modal
        this.locomotiveScroll?.lenisInstance?.options?.content?.addEventListener(
            'wheel',
            (event: Event) => {
                const targets = event.composedPath();
                const stopPropagation = targets.some((target) => {
                    const el = target as HTMLElement;
                    return (
                        el.classList?.contains('cky-modal') ||
                        el.classList?.contains('cky-consent-container')
                    );
                });
                if (stopPropagation) {
                    event.stopPropagation();
                }
            }
        );

        this.isInitialized = true;
    }

    static destroy() {
        this.locomotiveScroll?.destroy();
    }

    // =============================================================================
    // Methods
    // =============================================================================
    static start() {
        this.locomotiveScroll?.start();
    }

    static stop() {
        this.locomotiveScroll?.stop();
    }

    static addScrollElements(container: HTMLElement) {
        this.locomotiveScroll?.addScrollElements(container);
    }

    static removeScrollElements(container: HTMLElement) {
        this.locomotiveScroll?.removeScrollElements(container);
    }

    static scrollTo(target: lenisTargetScrollTo, options?: ILenisScrollToOptions) {
        this.locomotiveScroll?.scrollTo(target, options);
    }
}
