import http from 'http';
import config from 'config';
import secret from 'secret';

export default function ({ topic }) {
    const topics = {
        Booking: 'booking', Process: 'process', Prices: 'prices', Products: 'products',
        Softener: 'softener', Extras: 'extras', Tracking: 'tracking', PickupTimes: 'pickup_times',
        Turnaround: 'turnaround', Payment: 'payment', Changes: 'changes', Account: 'account',
        Preparation: 'preparation', Delicates: 'delicates', Stains: 'stains', Sorting: 'sorting',
        CareLabels: 'care_labels', Drying: 'drying', DetergentDose: 'detergent_dose', Contact: 'contact',
    };
    const id = topics[String(topic)];
    if (typeof id !== 'string') return { title: 'Laundry help', answer: 'Ask about booking, prices, tracking, or caring for your clothes.' };
    const origin = config.get('remote.url');
    const token = secret.get('laundryhub.apiToken');
    if (!origin || !/^https:\/\//.test(String(origin)) || !token) {
        return { title: 'Laundry help', answer: 'Laundryhub is not connected to Bixby yet. Please use the website assistant.' };
    }
    try {
        const reply = http.postUrl(String(origin).replace(/\/$/, '') + '/api/laundry-assistant/bixby', { topic: id }, {
            format: 'json', passAsJson: true, cacheTime: 0, timeoutMs: 10000,
            headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' },
        });
        if (!reply || typeof reply.answer !== 'string' || !reply.answer || typeof reply.title !== 'string') throw new Error('Invalid guide response');
        return { title: reply.title, answer: reply.answer };
    } catch (error) {
        // Keep connection failures and credentials out of spoken responses and logs.
        return { title: 'Laundry help', answer: 'I cannot reach Laundryhub right now. Please try again or use the website assistant.' };
    }
}
