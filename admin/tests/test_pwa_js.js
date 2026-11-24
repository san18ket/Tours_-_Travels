/**
 * Automated Verification of PWA Installation Flow & Native Prompt Mechanics
 */
const fs = require('fs');
const path = require('path');
const assert = require('assert');

console.log('=========================================================');
console.log('PWA JAVASCRIPT & INSTALLATION FLOW VERIFICATION');
console.log('=========================================================\n');

// 1. Static Code Analysis on tours-pwa.js
console.log('--- TEST 1: Static Code Inspection ---');
const jsCode = fs.readFileSync(path.join(__dirname, '../public/assets/js/tours-pwa.js'), 'utf8');

// Ensure no alert() or confirm()
assert(!/\balert\s*\(/.test(jsCode), 'Must NOT contain alert() calls');
assert(!/\bconfirm\s*\(/.test(jsCode), 'Must NOT contain confirm() calls');
console.log('✓ Zero alert() or confirm() dialogs in tours-pwa.js');

// Ensure native prompt is invoked on deferredInstallPrompt
assert(jsCode.includes('promptEvent.prompt()') || jsCode.includes('deferredInstallPrompt.prompt()'), 'Must call prompt() on deferred event');
assert(jsCode.includes('.userChoice'), 'Must await userChoice property');
console.log('✓ Native prompt().prompt() and .userChoice properly implemented');

// Ensure deferredInstallPrompt is cleared upon invocation
assert(jsCode.includes('deferredInstallPrompt = null;'), 'Must clear consumed deferredInstallPrompt');
console.log('✓ deferredInstallPrompt is properly cleared upon consumption');

// Ensure desktop manual fallback does NOT have misleading address bar primary instruction
assert(!jsCode.includes('Look for the Install icon on the right side of your browser address bar'), 'Must remove misleading address bar text');
console.log('✓ Misleading address bar instruction removed from desktop fallback');

// Ensure sw.js is updated with Network-First for manifest.json
const swCode = fs.readFileSync(path.join(__dirname, '../public/sw.js'), 'utf8');
assert(swCode.includes("url.pathname.endsWith('/manifest.json')"), 'sw.js must have dedicated manifest handling');
assert(swCode.includes("fetch(request).then"), 'sw.js manifest must fetch from network first');
assert(swCode.includes("v6.3"), 'sw.js version bumped to v6.3');
console.log('✓ sw.js has network-first caching strategy for manifest.json and version v6.3\n');

// 2. Behavioral DOM Simulation
console.log('--- TEST 2: DOM Simulation of Native Prompt Flow ---');

class MockElement {
    constructor(tag) {
        this.tagName = tag.toUpperCase();
        this.attributes = {};
        this.style = {};
        this.classList = new Set();
        this.classList.add = (c) => this.classList[c] = true;
        this.classList.remove = (c) => delete this.classList[c];
        this.classList.contains = (c) => Boolean(this.classList[c]);
        this._innerHTML = '';
        this.children = [];
        this.dataset = {};
        this.listeners = {};
    }
    set id(val) {
        this.attributes['id'] = val;
        elementsById[val] = this;
    }
    get id() { return this.attributes['id']; }
    setAttribute(k, v) {
        this.attributes[k] = String(v);
        if (k === 'id') elementsById[v] = this;
    }
    getAttribute(k) { return this.attributes[k] || null; }
    removeAttribute(k) { delete this.attributes[k]; }
    appendChild(child) {
        this.children.push(child);
        return child;
    }
    remove() {}
    addEventListener(ev, fn) {
        if (!this.listeners[ev]) this.listeners[ev] = [];
        this.listeners[ev].push(fn);
    }
    removeEventListener() {}
    querySelector(sel) {
        if (sel === '.install-btn-label') {
            return this.labelChild || (this.labelChild = new MockElement('span'));
        }
        if (sel === '.pwa-btn-install-primary') {
            return new MockElement('button');
        }
        return null;
    }
    querySelectorAll(sel) { return []; }
    focus() {}
    set innerHTML(html) {
        this._innerHTML = html;
        if (html.includes('id="pwaModalDynamicContent"')) {
            const dynamicContent = new MockElement('div');
            dynamicContent.id = 'pwaModalDynamicContent';
            this.appendChild(dynamicContent);
        }
    }
    get innerHTML() { return this._innerHTML; }
}

const storageMock = () => {
    let store = {};
    return {
        getItem: (k) => store[k] || null,
        setItem: (k, v) => { store[k] = String(v); },
        removeItem: (k) => { delete store[k]; },
        clear: () => { store = {}; }
    };
};

const localStorageMock = storageMock();
const sessionStorageMock = storageMock();

const eventListeners = {};
const windowObj = {
    addEventListener: (ev, fn) => {
        if (!eventListeners[ev]) eventListeners[ev] = [];
        eventListeners[ev].push(fn);
    },
    removeEventListener: () => {},
    matchMedia: () => ({ matches: false }),
    navigator: { standalone: false, serviceWorker: { getRegistration: async () => null, ready: Promise.resolve(null) } },
    location: { protocol: 'https:', hostname: 'localhost', reload: () => {} },
    requestAnimationFrame: (cb) => cb(),
    setTimeout: (cb, ms) => setTimeout(cb, ms || 10),
    clearTimeout: (id) => clearTimeout(id),
    setInterval: (cb) => setInterval(cb, 100000),
    clearInterval: (id) => clearInterval(id),
    fetch: async () => ({ ok: false, json: async () => ({}) }),
    localStorage: localStorageMock,
    sessionStorage: sessionStorageMock,
};

const elementsById = {};
const documentObj = {
    readyState: 'complete',
    documentElement: new MockElement('html'),
    addEventListener: (ev, fn) => {
        if (!eventListeners[ev]) eventListeners[ev] = [];
        eventListeners[ev].push(fn);
    },
    removeEventListener: () => {},
    getElementById: (id) => elementsById[id] || null,
    createElement: (tag) => {
        const el = new MockElement(tag);
        return el;
    },
    querySelector: (sel) => {
        if (sel === 'meta[name="application-name"]') {
            const el = new MockElement('meta');
            el.setAttribute('content', 'Acme Tours');
            return el;
        }
        if (sel === 'link[rel="apple-touch-icon"]') {
            const el = new MockElement('link');
            el.setAttribute('href', '/storage/company/pwa/pwa_icon_192.png');
            return el;
        }
        return null;
    },
    querySelectorAll: (sel) => [],
    body: new MockElement('body')
};

// Create a sandbox execution environment for tours-pwa.js
const vm = require('vm');
const sandbox = {
    window: windowObj,
    document: documentObj,
    navigator: windowObj.navigator,
    console: console,
    setTimeout: (cb, ms) => setTimeout(cb, ms || 10),
    clearTimeout: (id) => clearTimeout(id),
    setInterval: (cb) => setInterval(cb, 100000),
    clearInterval: (id) => clearInterval(id),
    requestAnimationFrame: (cb) => cb(),
    fetch: async () => ({ ok: false, json: async () => ({}) }),
    localStorage: localStorageMock,
    sessionStorage: sessionStorageMock,
};
vm.createContext(sandbox);
vm.runInContext(jsCode, sandbox);

// Verify window globals exposed
assert(typeof sandbox.window.startToursInstallation === 'function', 'startToursInstallation must be global function');
assert(typeof sandbox.window.openToursInstallModal === 'function', 'openToursInstallModal must be global function');
assert(typeof sandbox.window.closeToursInstallModal === 'function', 'closeToursInstallModal must be global function');
assert(typeof sandbox.window.showToursToast === 'function', 'showToursToast must be global function');
console.log('✓ tours-pwa.js initialized successfully in sandbox');

(async () => {
    // SCENARIO A: User opens modal before beforeinstallprompt fires (no native prompt available yet)
    console.log('--- TEST 3: Fallback state when native prompt is not available ---');
    sandbox.window.openToursInstallModal();
    const modal = sandbox.document.getElementById('toursPwaInstallModal');
    assert(modal, 'Modal element was created');
    const content = sandbox.document.getElementById('pwaModalDynamicContent');
    assert(content.innerHTML.includes('Installation is not currently available from this browser session'), 'Shows unavailable notice');
    assert(content.innerHTML.includes('View Installation Steps'), 'Primary button offers manual steps');
    console.log('✓ Truthful fallback displayed when native prompt is unavailable');

    // SCENARIO B: beforeinstallprompt event fires
    console.log('--- TEST 4: Capture beforeinstallprompt and update UI ---');
    let promptWasCalled = false;
    let userChoiceResolved = false;
    const mockInstallEvent = {
        preventDefault: () => {},
        prompt: async () => {
            promptWasCalled = true;
        },
        userChoice: new Promise((resolve) => {
            setTimeout(() => {
                userChoiceResolved = true;
                resolve({ outcome: 'accepted' });
            }, 10);
        })
    };

    const bipListeners = eventListeners['beforeinstallprompt'];
    assert(bipListeners && bipListeners.length > 0, 'beforeinstallprompt listener is registered');
    bipListeners[0](mockInstallEvent);

    // Modal was already open, should now reflect "Install Now"
    assert(content.innerHTML.includes('Install Now'), 'Modal refreshed to show Install Now');
    console.log('✓ Modal reactively upgraded to "Install Now" upon beforeinstallprompt');

    // SCENARIO C: User clicks "Install Now" (Accepted Flow)
    console.log('--- TEST 5: User clicks "Install Now" and accepts native prompt ---');
    assert(!promptWasCalled, 'prompt() must NOT have been called automatically');
    await sandbox.window.startToursInstallation();
    assert(promptWasCalled, 'prompt() was invoked upon user clicking Install Now');
    assert(userChoiceResolved, 'userChoice was awaited');
    console.log('✓ Native prompt was triggered only after explicit user action');

    // SCENARIO D: Prompt must be cleared and not reused
    console.log('--- TEST 6: Consumed prompt is cleared ---');
    promptWasCalled = false;
    await sandbox.window.startToursInstallation();
    assert(!promptWasCalled, 'prompt() must NOT be called a second time after consumption');
    assert(content.innerHTML.includes('Install Acme Tours on Desktop'), 'Fell back to manual instructions');
    console.log('✓ Consumed prompt was safely cleared and routed to instructions fallback');

    // SCENARIO E: User dismisses native prompt
    console.log('--- TEST 7: Dismissed native prompt handling ---');
    const mockDismissEvent = {
        preventDefault: () => {},
        prompt: async () => {},
        userChoice: Promise.resolve({ outcome: 'dismissed' })
    };
    bipListeners[0](mockDismissEvent);
    await sandbox.window.startToursInstallation();
    assert(content.innerHTML.includes('Installation was cancelled. You can try again later.'), 'Notice shows cancellation message');
    console.log('✓ Dismissal handled gracefully with professional message and no alerts');

    // SCENARIO F: appinstalled event
    console.log('--- TEST 8: Genuine appinstalled event handling ---');
    const appInstalledListeners = eventListeners['appinstalled'];
    assert(appInstalledListeners && appInstalledListeners.length > 0, 'appinstalled listener is registered');
    appInstalledListeners[0]();

    const toastContainer = sandbox.document.getElementById('toursToastContainer');
    assert(toastContainer, 'Toast container exists');
    assert(toastContainer.children.length > 0, 'Toast was added to DOM');
    const lastToast = toastContainer.children[toastContainer.children.length - 1];
    assert(lastToast.innerHTML.includes('installed successfully'), 'Toast shows genuine installation success');
    console.log('✓ appinstalled confirms installation cleanly with success toast and without fake progress');

    console.log('\n=========================================================');
    console.log('ALL PWA INSTALLATION TESTS PASSED PERFECTLY!');
    console.log('=========================================================');
    process.exit(0);
})();
