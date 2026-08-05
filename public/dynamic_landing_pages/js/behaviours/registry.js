import { initRecurringCountdown } from './recurring-countdown.js';
import { initOrderSubmission } from './order-submission.js';

const behaviourRegistry = {
    'recurring-countdown': initRecurringCountdown,
    'order-submission': initOrderSubmission,
};

export function getBehaviour(key) {
    return behaviourRegistry[key] ?? null;
}
