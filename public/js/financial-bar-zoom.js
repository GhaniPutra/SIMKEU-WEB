function getFinancialZoomDelta(event) {
    const deltaY = Number(event.deltaY) || 0;
    if (deltaY === 0) return null;

    // Ctrl+wheel is the browser signal most consistently emitted for a
    // trackpad pinch. Keep its pixel delta at high resolution for accumulation.
    if (event.ctrlKey) return deltaY * (event.deltaMode === 1 ? 40 : 1);

    const legacyWheelDelta = Number(event.wheelDeltaY || event.wheelDelta || 0);
    const hasDiscreteLegacyNotch = legacyWheelDelta !== 0
        && Math.abs(legacyWheelDelta) % 120 === 0;
    // Firefox commonly reports a physical wheel as integral line units and
    // does not expose wheelDelta. Pixel-mode scrolls stay with the page unless
    // a browser also reports a legacy wheel notch.
    const hasFirefoxLineWheel = event.deltaMode === 1 && Number.isInteger(deltaY);
    if (!hasDiscreteLegacyNotch && !hasFirefoxLineWheel) return null;

    return deltaY * (event.deltaMode === 1 ? 40 : 1);
}

window.attachFinancialBarZoom = function (chart, canvas) {
    const lastIndex = chart.data.labels.length - 1;
    if (lastIndex < 1) return;

    let visibleRange = { min: 0, max: lastIndex };
    let cumulativeZoomLog = 0;
    let previousTouchDistance = null;
    let dragState = null;
    let pendingTrackpadPan = 0;

    const clamp = (value, min, max) => Math.min(max, Math.max(min, value));
    const visibleCount = () => visibleRange.max - visibleRange.min + 1;
    const isZoomed = () => visibleCount() < lastIndex + 1;
    const updateCursor = () => {
        if (!canvas.style) return;
        canvas.style.cursor = dragState ? 'grabbing' : (isZoomed() ? 'grab' : '');
    };

    const getAnchor = (clientX) => {
        const bounds = canvas.getBoundingClientRect();
        const pointerX = clamp(clientX - bounds.left, chart.chartArea.left, chart.chartArea.right);
        const value = chart.scales.x.getValueForPixel(pointerX);

        return Number.isFinite(value)
            ? clamp(Math.round(value), visibleRange.min, visibleRange.max)
            : (visibleRange.min + visibleRange.max) / 2;
    };

    const setHorizontalRange = (min) => {
        const count = visibleCount();
        const nextMin = clamp(Math.round(min), 0, lastIndex + 1 - count);
        if (nextMin === visibleRange.min) return false;

        visibleRange = { min: nextMin, max: nextMin + count - 1 };
        chart.options.scales.x.min = visibleRange.min;
        chart.options.scales.x.max = visibleRange.max;
        chart.update('none');
        updateCursor();

        return true;
    };

    const panWithTrackpad = (deltaX, deltaMode) => {
        if (!isZoomed() || deltaMode === 2) {
            pendingTrackpadPan = 0;
            return false;
        }

        const categoryWidth = chart.chartArea.width / visibleCount();
        if (!(categoryWidth > 0)) return false;

        pendingTrackpadPan += deltaX * (deltaMode === 1 ? 40 : 1);
        const categoryShift = Math.trunc(pendingTrackpadPan / categoryWidth);
        if (categoryShift === 0) return false;

        const changed = setHorizontalRange(visibleRange.min + categoryShift);
        if (changed) {
            pendingTrackpadPan -= categoryShift * categoryWidth;
        } else {
            pendingTrackpadPan = 0;
        }

        return changed;
    };

    const updateVerticalRange = () => {
        const firstIndex = Math.max(0, visibleRange.min);
        const lastVisibleIndex = Math.min(lastIndex, visibleRange.max);
        const visibleMaximum = chart.data.datasets.reduce((maximum, dataset) => {
            const datasetMaximum = dataset.data
                .slice(firstIndex, lastVisibleIndex + 1)
                .reduce((currentMaximum, value) => Math.max(currentMaximum, Number(value) || 0), 0);

            return Math.max(maximum, datasetMaximum);
        }, 0);

        if (visibleRange.min === 0 && visibleRange.max === lastIndex) {
            delete chart.options.scales.y.max;
        } else {
            chart.options.scales.y.max = visibleMaximum > 0 ? visibleMaximum * 1.12 : 1;
        }
    };

    const zoomByLogDelta = (logDelta, anchor) => {
        if (!Number.isFinite(logDelta) || logDelta === 0) return false;

        const totalCount = lastIndex + 1;
        const minimumZoomLog = Math.log(1 / totalCount);
        cumulativeZoomLog = clamp(cumulativeZoomLog + clamp(logDelta, -0.24, 0.24), minimumZoomLog, 0);
        const currentCount = visibleRange.max - visibleRange.min + 1;
        const nextCount = clamp(Math.round(totalCount * Math.exp(cumulativeZoomLog)), 1, totalCount);
        if (nextCount === currentCount) return false;

        const anchorIndex = clamp(Math.round(anchor), visibleRange.min, visibleRange.max);
        const anchorRatio = currentCount > 1
            ? (anchorIndex - visibleRange.min) / (currentCount - 1)
            : 0.5;
        const min = clamp(
            Math.round(anchorIndex - anchorRatio * (nextCount - 1)),
            0,
            lastIndex + 1 - nextCount
        );
        const max = min + nextCount - 1;

        visibleRange = { min, max };
        chart.options.scales.x.min = min;
        chart.options.scales.x.max = max;
        updateVerticalRange();
        chart.update('none');
        updateCursor();

        return true;
    };

    const reset = () => {
        visibleRange = { min: 0, max: lastIndex };
        cumulativeZoomLog = 0;
        chart.options.scales.x.min = 0;
        chart.options.scales.x.max = lastIndex;
        delete chart.options.scales.y.max;
        chart.update('none');
        pendingTrackpadPan = 0;
        updateCursor();
    };

    canvas.addEventListener('wheel', (event) => {
        const horizontalGesture = !event.ctrlKey
            && isZoomed()
            && Math.abs(event.deltaX) > Math.abs(event.deltaY)
            && event.deltaX !== 0;

        if (horizontalGesture) {
            const changed = panWithTrackpad(event.deltaX, event.deltaMode);
            if (changed || pendingTrackpadPan !== 0) event.preventDefault();
            return;
        }

        pendingTrackpadPan = 0;
        const zoomDelta = getFinancialZoomDelta(event);
        if (zoomDelta === null) return;

        // A recognized zoom gesture belongs to the chart even when rounding
        // has not yet crossed a whole-category boundary or a limit is reached.
        zoomByLogDelta(zoomDelta * 0.0015, getAnchor(event.clientX));
        event.preventDefault();
    }, { passive: false });

    canvas.addEventListener('pointerdown', (event) => {
        if (!isZoomed() || event.pointerType !== 'mouse' || event.button !== 0) return;

        const categoryWidth = chart.chartArea.width / visibleCount();
        if (!(categoryWidth > 0)) return;

        dragState = {
            pointerId: event.pointerId,
            startX: event.clientX,
            startMin: visibleRange.min,
            categoryWidth
        };
        pendingTrackpadPan = 0;
        if (typeof canvas.setPointerCapture === 'function') {
            canvas.setPointerCapture(event.pointerId);
        }
        updateCursor();
    });

    canvas.addEventListener('pointermove', (event) => {
        if (!dragState || event.pointerId !== dragState.pointerId) return;

        const categoryShift = Math.round((dragState.startX - event.clientX) / dragState.categoryWidth);
        setHorizontalRange(dragState.startMin + categoryShift);
        if (event.cancelable) event.preventDefault();
    }, { passive: false });

    const stopDrag = (event) => {
        if (!dragState || (event && event.pointerId !== dragState.pointerId)) return;

        dragState = null;
        pendingTrackpadPan = 0;
        updateCursor();
    };

    canvas.addEventListener('pointerup', stopDrag);
    canvas.addEventListener('pointercancel', stopDrag);
    canvas.addEventListener('lostpointercapture', stopDrag);

    canvas.addEventListener('dblclick', (event) => {
        if (visibleRange.min === 0 && visibleRange.max === lastIndex) return;

        event.preventDefault();
        reset();
    });

    canvas.addEventListener('touchstart', (event) => {
        if (event.touches.length === 2) {
            const [first, second] = event.touches;
            previousTouchDistance = Math.hypot(second.clientX - first.clientX, second.clientY - first.clientY);
        }
    }, { passive: true });

    canvas.addEventListener('touchmove', (event) => {
        if (event.touches.length !== 2 || !previousTouchDistance) return;

        const [first, second] = event.touches;
        const distance = Math.hypot(second.clientX - first.clientX, second.clientY - first.clientY);
        const midpointX = (first.clientX + second.clientX) / 2;
        zoomByLogDelta(Math.log(previousTouchDistance / distance), getAnchor(midpointX));
        previousTouchDistance = distance;
        event.preventDefault();
    }, { passive: false });

    const clearTouchZoom = () => {
        previousTouchDistance = null;
    };
    canvas.addEventListener('touchend', clearTouchZoom, { passive: true });
    canvas.addEventListener('touchcancel', clearTouchZoom, { passive: true });
};
