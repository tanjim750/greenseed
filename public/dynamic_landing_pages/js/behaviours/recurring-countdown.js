function parseStartTime(value) {
    if (!value) {
        return Date.now();
    }

    const timestamp = Date.parse(String(value).replace(' ', 'T'));

    return Number.isNaN(timestamp) ? Date.now() : timestamp;
}

function formatPart(value) {
    return String(value).padStart(2, '0');
}

function formatRemaining(milliseconds) {
    const totalSeconds = Math.max(0, Math.floor(milliseconds / 1000));
    const days = Math.floor(totalSeconds / 86400);
    const hours = Math.floor((totalSeconds % 86400) / 3600);
    const minutes = Math.floor((totalSeconds % 3600) / 60);
    const seconds = totalSeconds % 60;

    return {
        days,
        hours,
        minutes,
        seconds,
        text: `${days}d ${formatPart(hours)}h ${formatPart(minutes)}m ${formatPart(seconds)}s`,
    };
}

export function initRecurringCountdown(root, config) {
    const output = root.querySelector('[data-countdown-output]');

    if (!output) {
        return null;
    }

    const parts = {
        days: output.querySelector('[data-countdown-part="days"]'),
        hours: output.querySelector('[data-countdown-part="hours"]'),
        minutes: output.querySelector('[data-countdown-part="minutes"]'),
        seconds: output.querySelector('[data-countdown-part="seconds"]'),
    };
    const hasPartOutputs = Object.values(parts).some(Boolean);

    const durationHours = Number(config.durationHours);
    const durationMilliseconds = Number.isFinite(durationHours) && durationHours > 0
        ? durationHours * 60 * 60 * 1000
        : 0;
    const startsAt = parseStartTime(config.startsAt);
    const endsAt = startsAt + durationMilliseconds;

    const render = () => {
        const remaining = formatRemaining(endsAt - Date.now());

        if (hasPartOutputs) {
            if (parts.days) {
                parts.days.textContent = formatPart(remaining.days);
            }
            if (parts.hours) {
                parts.hours.textContent = formatPart(remaining.hours);
            }
            if (parts.minutes) {
                parts.minutes.textContent = formatPart(remaining.minutes);
            }
            if (parts.seconds) {
                parts.seconds.textContent = formatPart(remaining.seconds);
            }
        } else {
            output.textContent = remaining.text;
        }

        output.dataset.days = String(remaining.days);
        output.dataset.hours = String(remaining.hours);
        output.dataset.minutes = String(remaining.minutes);
        output.dataset.seconds = String(remaining.seconds);

        if (endsAt <= Date.now()) {
            output.dataset.expired = 'true';
            clearInterval(timer);
        }
    };

    const timer = window.setInterval(render, 1000);
    render();

    return () => {
        clearInterval(timer);
    };
}
