import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import { test } from 'node:test';
import { runInNewContext } from 'node:vm';

const source = readFileSync(new URL('../resources/js/native-bridge.js', import.meta.url), 'utf8');

function setup() {
    const listeners = new Map();
    let reloads = 0;
    const document = {
        body: { appendChild() {} },
        querySelectorAll: () => [],
        createElement: () => ({ style: {}, dataset: {}, isConnected: true, setAttribute() {} }),
        addEventListener: (name, handler) => listeners.set(name, handler),
    };
    const window = { scrollY: 0, location: { reload: () => reloads++ } };
    runInNewContext(`${source}\ninitPullToRefresh({ impact() {} });`, { document, window });
    const target = { parentElement: document.body, closest: () => null };
    const emit = (name, y = 0, element = target, count = 1) => listeners.get(name)({
        target: element,
        touches: Array.from({ length: count }, () => ({ clientY: y })),
    });
    const pull = (element = target) => {
        emit('touchstart', 0, element);
        emit('touchmove', 160, element);
        emit('touchend', 160, element);
    };
    return { emit, pull, window, reloads: () => reloads };
}

test('ordinary content can refresh before and after a gesture on the font control', () => {
    const app = setup();
    app.pull();
    assert.equal(app.reloads(), 1);
    app.pull({ closest: (selector) => selector === '[data-ptr-ignore]' ? {} : null });
    assert.equal(app.reloads(), 1);
    app.pull();
    assert.equal(app.reloads(), 2);
});

for (const interruption of ['touchcancel', 'livewire:navigating', 'multitouch', 'upward']) {
    test(`${interruption} cancels an armed pull without locking the next gesture`, () => {
        const app = setup();
        app.emit('touchstart');
        app.emit('touchmove', 160);
        if (interruption === 'multitouch') app.emit('touchmove', 160, undefined, 2);
        else if (interruption === 'upward') app.emit('touchmove', -10);
        else app.emit(interruption);
        app.emit('touchend');
        assert.equal(app.reloads(), 0);
        app.pull();
        assert.equal(app.reloads(), 1);
    });
}

test('a scrolled page does not refresh', () => {
    const app = setup();
    app.window.scrollY = 100;
    app.pull();
    assert.equal(app.reloads(), 0);
});
