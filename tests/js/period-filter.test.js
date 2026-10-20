import assert from 'node:assert/strict';
import { readFile } from 'node:fs/promises';
import { runInNewContext } from 'node:vm';
import test from 'node:test';

function matchesSelector(element, selector) {
    if (selector === '[data-period-preset]') return element.dataset.periodPreset !== undefined;
    if (selector === '[data-date]') return element.dataset.date !== undefined;
    if (selector === '[data-calendar-nav="previous"]') return element.dataset.calendarNav === 'previous';
    if (selector === '[data-calendar-nav="next"]') return element.dataset.calendarNav === 'next';
    if (selector === 'a[href]') return element.tagName === 'A' && element.hasAttribute('href');
    return false;
}

class FakeElement {
    constructor(tagName, ownerDocument) {
        this.tagName = tagName.toUpperCase();
        this.ownerDocument = ownerDocument;
        this.children = [];
        this.parentNode = null;
        this.dataset = {};
        this.attributes = new Map();
        this.listeners = new Map();
        this.hidden = false;
        this.disabled = false;
        this.textContent = '';
        this.title = '';
        this.type = '';
        this.tabIndex = 0;
        this._classNames = new Set();
        this.classList = {
            add: (...names) => names.forEach((name) => this._classNames.add(name)),
            remove: (...names) => names.forEach((name) => this._classNames.delete(name)),
            contains: (name) => this._classNames.has(name),
            toggle: (name, force) => {
                const shouldAdd = force === undefined ? !this._classNames.has(name) : force;
                if (shouldAdd) this._classNames.add(name);
                else this._classNames.delete(name);
                return shouldAdd;
            }
        };
    }

    get className() {
        return [...this._classNames].join(' ');
    }

    set className(value) {
        this._classNames = new Set(String(value).split(/\s+/).filter(Boolean));
    }

    setAttribute(name, value) {
        this.attributes.set(name, String(value));
    }

    getAttribute(name) {
        return this.attributes.get(name) ?? null;
    }

    hasAttribute(name) {
        return this.attributes.has(name);
    }

    appendChild(child) {
        child.remove();
        child.parentNode = this;
        this.children.push(child);
        return child;
    }

    replaceChildren(...children) {
        this.children.forEach((child) => { child.parentNode = null; });
        this.children = [];
        children.forEach((child) => this.appendChild(child));
    }

    remove() {
        if (!this.parentNode) return;
        this.parentNode.children = this.parentNode.children.filter((child) => child !== this);
        this.parentNode = null;
    }

    contains(target) {
        return this === target || this.children.some((child) => child.contains(target));
    }

    addEventListener(type, callback) {
        if (!this.listeners.has(type)) this.listeners.set(type, []);
        this.listeners.get(type).push(callback);
    }

    querySelectorAll(selector) {
        const matches = [];
        const visit = (element) => element.children.forEach((child) => {
            if (matchesSelector(child, selector)) matches.push(child);
            visit(child);
        });
        visit(this);
        return matches;
    }

    querySelector(selector) {
        return this.querySelectorAll(selector)[0] ?? null;
    }

    closest(selector) {
        for (let element = this; element; element = element.parentNode) {
            if (matchesSelector(element, selector)) return element;
        }
        return null;
    }

    focus() {
        this.ownerDocument.activeElement = this;
    }
}

class FakeDocument {
    constructor() {
        this.listeners = new Map();
        this.elementsById = new Map();
        this.activeElement = null;
        this.cookie = '';
        this.body = new FakeElement('body', this);
        this.documentElement = new FakeElement('html', this);
    }

    createElement(tagName) {
        return new FakeElement(tagName, this);
    }

    getElementById(id) {
        return this.elementsById.get(id) ?? null;
    }

    querySelectorAll() {
        return [];
    }

    addEventListener(type, callback) {
        if (!this.listeners.has(type)) this.listeners.set(type, []);
        this.listeners.get(type).push(callback);
    }

    register(id, element) {
        this.elementsById.set(id, element);
        return element;
    }
}

function buildPeriodFilterFixture() {
    const document = new FakeDocument();
    const filter = document.register('global-period-filter', document.createElement('div'));
    filter.dataset.timezone = 'Asia/Jakarta';
    document.body.appendChild(filter);

    const trigger = document.register('global-period-trigger', document.createElement('button'));
    const triggerLabel = document.register('global-period-label', document.createElement('span'));
    const menu = document.register('global-period-menu', document.createElement('div'));
    menu.hidden = true;
    filter.appendChild(trigger);
    trigger.appendChild(triggerLabel);
    filter.appendChild(menu);

    const presetButtons = ['all', 'today', 'week', 'month'].map((period) => {
        const button = document.createElement('button');
        button.dataset.periodPreset = period;
        menu.appendChild(button);
        return button;
    });
    const customToggle = document.register('global-period-custom-toggle', document.createElement('button'));
    const calendarPanel = document.register('global-period-calendar-panel', document.createElement('section'));
    const calendarContainer = document.register('global-period-calendars', document.createElement('div'));
    const selectionLabel = document.register('global-period-selection', document.createElement('p'));
    const applyButton = document.register('global-period-apply', document.createElement('button'));
    calendarPanel.hidden = true;
    menu.appendChild(customToggle);
    menu.appendChild(calendarPanel);
    calendarPanel.appendChild(calendarContainer);
    calendarPanel.appendChild(selectionLabel);
    calendarPanel.appendChild(applyButton);

    const previousMonth = document.createElement('button');
    previousMonth.dataset.calendarNav = 'previous';
    const nextMonth = document.createElement('button');
    nextMonth.dataset.calendarNav = 'next';
    menu.appendChild(previousMonth);
    menu.appendChild(nextMonth);

    const outside = document.createElement('button');
    document.body.appendChild(outside);

    return { document, filter, trigger, triggerLabel, menu, presetButtons, customToggle, calendarPanel, calendarContainer, selectionLabel, applyButton, previousMonth, nextMonth, outside };
}

function dispatchClick(target, document) {
    const path = [];
    for (let element = target; element; element = element.parentNode) path.push(element);
    const event = {
        type: 'click', target, button: 0, defaultPrevented: false,
        metaKey: false, ctrlKey: false, shiftKey: false, altKey: false,
        composedPath: () => path
    };

    // Simulasikan jalur bubbling yang tetap menyimpan ancestor meski handler melepas target dari DOM.
    path.forEach((element) => element.listeners?.get('click')?.forEach((listener) => listener(event)));
    document.listeners.get('click')?.forEach((listener) => listener(event));
}

function dispatchEscape(document) {
    const event = { key: 'Escape', preventDefault() {} };
    document.listeners.get('keydown')?.forEach((listener) => listener(event));
}

function clickDate(fixture, value) {
    const button = fixture.calendarContainer.querySelectorAll('[data-date]')
        .find((day) => day.dataset.date === value);
    assert.ok(button, `Calendar date ${value} should be rendered`);
    dispatchClick(button, fixture.document);
    return button;
}

async function initializeFilter(fixture) {
    const source = await readFile(new URL('../../public/js/layout/layout.js', import.meta.url), 'utf8');
    const location = new URL('https://simkeu.test/unfiltered-page');
    const window = {
        innerWidth: 1200,
        location,
        history: { replaceState() {} },
        addEventListener() {}
    };
    runInNewContext(source, {
        document: fixture.document,
        window,
        Element: FakeElement,
        HTMLFormElement: class HTMLFormElement {},
        URL,
        URLSearchParams,
        requestAnimationFrame(callback) { callback(); },
        localStorage: { getItem() { return null; }, setItem() {} },
        getComputedStyle() { return { opacity: '1', transform: 'none' }; }
    });
}

test('calendar rerender keeps the dropdown open until Apply; outside click and Escape still close it', async () => {
    const fixture = buildPeriodFilterFixture();
    await initializeFilter(fixture);

    dispatchClick(fixture.trigger, fixture.document);
    assert.equal(fixture.menu.hidden, false);
    dispatchClick(fixture.customToggle, fixture.document);
    assert.equal(fixture.calendarPanel.hidden, false);

    const currentMonth = new Intl.DateTimeFormat('en-CA', {
        timeZone: 'Asia/Jakarta', year: 'numeric', month: '2-digit'
    }).formatToParts(new Date()).reduce((parts, part) => {
        if (part.type !== 'literal') parts[part.type] = part.value;
        return parts;
    }, {});
    const date = (day) => `${currentMonth.year}-${currentMonth.month}-${String(day).padStart(2, '0')}`;

    clickDate(fixture, date(5));
    assert.equal(fixture.menu.hidden, false, 'first date click must leave the dropdown open');
    assert.equal(fixture.applyButton.disabled, true, 'partial ranges cannot be applied');

    clickDate(fixture, date(12));
    assert.equal(fixture.menu.hidden, false, 'second date click must leave the dropdown open');
    assert.equal(fixture.applyButton.disabled, false);
    assert.equal(fixture.calendarContainer.querySelectorAll('[data-date]')
        .find((day) => day.dataset.date === date(8)).classList.contains('is-in-range'), true);

    clickDate(fixture, date(20));
    assert.equal(fixture.menu.hidden, false, 'changing a completed range must keep the dropdown open');
    assert.equal(fixture.applyButton.disabled, true, 'a new start date begins a new partial range');
    assert.equal(fixture.calendarContainer.querySelectorAll('[data-date]')
        .find((day) => day.dataset.date === date(8)).classList.contains('is-in-range'), false);
    clickDate(fixture, date(7));
    clickDate(fixture, date(10));
    assert.equal(fixture.calendarContainer.querySelectorAll('[data-date]')
        .find((day) => day.dataset.date === date(8)).classList.contains('is-in-range'), true);

    dispatchClick(fixture.nextMonth, fixture.document);
    assert.equal(fixture.menu.hidden, false, 'next-month navigation must stay inside the dropdown');
    dispatchClick(fixture.previousMonth, fixture.document);
    assert.equal(fixture.menu.hidden, false, 'previous-month navigation must stay inside the dropdown');

    dispatchClick(fixture.applyButton, fixture.document);
    assert.equal(fixture.menu.hidden, true, 'Apply closes the dropdown after the valid range is committed');
    assert.match(fixture.triggerLabel.textContent, /–/);

    const presets = [
        ['all', 'Semua Periode'], ['today', 'Hari Ini'], ['week', 'Minggu Ini'], ['month', 'Bulan Ini']
    ];
    for (const [index, [period, label]] of presets.entries()) {
        dispatchClick(fixture.trigger, fixture.document);
        dispatchClick(fixture.presetButtons[index], fixture.document);
        assert.equal(fixture.menu.hidden, true, `${period} preset closes after applying`);
        assert.equal(fixture.triggerLabel.textContent, label, `${period} preset updates its label`);
    }

    dispatchClick(fixture.trigger, fixture.document);
    dispatchClick(fixture.outside, fixture.document);
    assert.equal(fixture.menu.hidden, true, 'outside click still closes the dropdown');

    dispatchClick(fixture.trigger, fixture.document);
    dispatchEscape(fixture.document);
    assert.equal(fixture.menu.hidden, true, 'Escape still closes the dropdown');
    assert.equal(fixture.document.activeElement, fixture.trigger, 'Escape returns focus to the trigger');
});
