import { toDash } from '@scripts/utils/string';
import SwupFormsPlugin from '@swup/forms-plugin';
import SwupPreloadPlugin from '@swup/preload-plugin';
import SwupScriptsPlugin from '@swup/scripts-plugin';
// import SwupFragmentPlugin from '@swup/fragment-plugin';
import Swup, { type HookArguments, type HookDefaultHandler, type Visit } from 'swup';
import { Scroll } from '@scripts/classes/Scroll';

export class Transitions {
    static readonly IS_FIRST_LOADED = 'is-first-loaded';
    static readonly READY_CLASS = 'is-ready';
    static readonly TRANSITION_CLASS = 'is-transitioning';
    static readonly CUSTOM_EVENTS = {
        GO_TO: 'transitions.goTo',
        VISIT_START: 'visit.start'
    };

    private readonly onVisitStartBind: any;
    private readonly beforeContentReplaceBind: any;
    private readonly onContentReplaceBind: any;
    private readonly onAnimationInEndBind: any;
    private readonly onAnimationOutStartBind: any;

    private rulesPaths: Record<string, string>;

    private swup: Swup | undefined;

    constructor() {
        this.onVisitStartBind = this.onVisitStart.bind(this);
        this.beforeContentReplaceBind = this.beforeContentReplace.bind(this);
        this.onContentReplaceBind = this.onContentReplace.bind(this);
        this.onAnimationInEndBind = this.onAnimationInEnd.bind(this);
        this.onAnimationOutStartBind = this.onAnimationOutStart.bind(this);
    }

    // =============================================================================
    // Lifecycle
    // =============================================================================
    init() {
        // const rulesPathsEl = document.getElementById('rulesPaths');
        // this.rulesPaths = JSON.parse(rulesPathsEl.innerHTML);
        // rulesPathsEl.remove();

        this.initSwup();
        this.bindEvents();

        requestAnimationFrame(() => {
            document.documentElement.classList.add(Transitions.IS_FIRST_LOADED);
            document.documentElement.classList.add(Transitions.READY_CLASS);
        });
    }

    destroy() {
        this.unbindEvents();

        this.swup?.destroy();
    }

    // =============================================================================
    // Methods
    // =============================================================================
    initSwup() {
        this.swup = new Swup({
            animateHistoryBrowsing: true,
            linkSelector: 'a[href]:not(#wpadminbar a)',
            linkToSelf: 'navigate',
            plugins: [
                new SwupPreloadPlugin({
                    preloadHoveredLinks: true,
                    preloadInitialPage: !import.meta.env.DEV
                }),
                new SwupScriptsPlugin(),
                new SwupFormsPlugin({
                    stripEmptyParams: true
                })
                // new SwupFragmentPlugin({
                //     rules: [
                //         {
                //             from: [
                //                 `${this.rulesPaths['job-offers-listing']}`,
                //                 `${this.rulesPaths['job-offers-listing']}(\\?.*)`
                //             ],
                //             to: [
                //                 `${this.rulesPaths['job-offers-listing']}`,
                //                 `${this.rulesPaths['job-offers-listing']}(\\?.*)`
                //             ],
                //             containers: ['#listing'],
                //             name: 'listing-update'
                //         }
                //     ]
                // })
            ]
        });
    }

    bindEvents() {
        window.addEventListener(Transitions.CUSTOM_EVENTS.GO_TO, this.goTo);

        this.swup.hooks.on('visit:start', this.onVisitStartBind);
        this.swup.hooks.before('content:replace', this.beforeContentReplaceBind);
        this.swup.hooks.on('content:replace', this.onContentReplaceBind);
        this.swup.hooks.on('animation:in:end', this.onAnimationInEndBind);
        this.swup.hooks.on('animation:out:start', this.onAnimationOutStartBind);

        this.swup.hooks.replace('form:submit', this.onFormSubmit);

        this.swup.hooks.on('fetch:error', (e) => {
            console.log('fetch:error:', e);
            debugger;
        });
        this.swup.hooks.on('fetch:timeout', (e) => {
            console.log('fetch:timeout:', e);
            debugger;
        });

        this.swup.hooks.on('scroll:anchor', this.onScrollAnchor);
    }

    unbindEvents() {
        window.removeEventListener(Transitions.CUSTOM_EVENTS.GO_TO, this.goTo);
    }

    /**
     * Retrieve HTML dataset on next container and update our real html element dataset accordingly
     *
     * @param visit: Visit
     */
    updateDocumentAttributes(visit: Visit) {
        const parser = new DOMParser();
        const nextDOM = parser.parseFromString(visit.to.html, 'text/html');
        const nextHTML = nextDOM.querySelector('html');
        const newDataset = {
            ...nextHTML?.dataset
        };

        Object.entries(newDataset).forEach(([key, val]) => {
            document.documentElement.setAttribute(`data-${toDash(key)}`, val ?? '');
        });
    }

    // =============================================================================
    // Hooks
    // =============================================================================

    /**
     * On `transition.goTo` custom event
     * Navigate to a new page
     *
     * @param e: CustomEvent
     */
    goTo = (e: CustomEvent) => {
        this.swup.navigate(e.detail);
    };

    /**
     * On visit:start
     * Transition to a new page begins
     *
     * @see https://swup.js.org/hooks/#visit-start
     * @param visit: Visit
     */
    onVisitStart(visit) {
        // Dispatch custom event
        window.dispatchEvent(
            new CustomEvent(Transitions.CUSTOM_EVENTS.VISIT_START, { detail: visit })
        );

        document.documentElement.classList.add(Transitions.TRANSITION_CLASS);
        document.documentElement.classList.remove(Transitions.READY_CLASS);
    }

    /**
     * On before:content:replace
     * The old content of the page is replaced by the new content.
     *
     * @see https://swup.js.org/hooks/#content-replace
     * @param visit: Visit
     */
    beforeContentReplace() {
        Scroll?.destroy();
    }

    /**
     * On content:replace
     * The old content of the page is replaced by the new content.
     *
     * @see https://swup.js.org/hooks/#content-replace
     * @param visit: Visit
     */
    onContentReplace(visit: Visit) {
        Scroll?.init();
        this.updateDocumentAttributes(visit);
    }

    /**
     * On animation:out:start
     * Current content starts animating out. Class `.is-animating` is added.
     *
     * @see https://swup.js.org/hooks/#animation-out-start
     * @param visit: Visit
     */
    onAnimationOutStart() {}

    /**
     * On animation:in:end
     * New content finishes animating out.
     *
     * @see https://swup.js.org/hooks/#animation-in-end
     * @param visit: Visit
     */
    onAnimationInEnd() {
        document.documentElement.classList.remove(Transitions.TRANSITION_CLASS);
        document.documentElement.classList.add(Transitions.READY_CLASS);
    }

    /**
     * Cancel consecutive submissions for the same form (caused by
     * double-clicking the submit button) to avoid processing the
     * same submission multiple times.
     *
     * This also avoids Swup accidentally serving the wrong response
     * which may be invalid (for example, validating a CAPTCHA token twice)
     * which would confuse the visitor and invite them to try submitting again.
     */
    onFormSubmit = (
        visit: Visit,
        args: HookArguments<'form:submit'>,
        defaultHandler: HookDefaultHandler<'form:submit'>
    ) => {
        if (!args.el || !args.event) {
            return defaultHandler(visit, args);
        }

        if (!args.el.dataset?.swupFormSubmitted) {
            args.el.dataset.swupFormSubmitted = 'true';
            return defaultHandler(visit, args);
        }

        args.event?.preventDefault();
    };

    // https://github.com/swup/swup/issues/954#issuecomment-2366895630
    onScrollAnchor = (visit, { hash }) => {
        const el = this.swup.getAnchorElement(hash);
        if (el instanceof HTMLElement) {
            const tabindex = el.getAttribute('tabindex');
            el.setAttribute('tabindex', '-1');
            el.focus({ preventScroll: true });
            if (tabindex !== null) {
                el.setAttribute('tabindex', tabindex);
            }
        }
    };
}
