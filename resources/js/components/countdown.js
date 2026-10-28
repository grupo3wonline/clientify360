// Live countdown to a subscription renewal/expiry date.
// Recomputed from Date.now() on every tick, so a throttled background tab never drifts.
export default function countdown(endsAt) {
    const end = new Date(endsAt).getTime();
    const pad = (value) => String(value).padStart(2, '0');

    return {
        days: 0,
        hours: '00',
        minutes: '00',
        seconds: '00',
        finished: false,
        timer: null,

        init() {
            this.tick();
            this.timer = setInterval(() => this.tick(), 1000);
        },

        destroy() {
            clearInterval(this.timer);
        },

        tick() {
            const remaining = Math.max(0, Math.floor((end - Date.now()) / 1000));

            this.finished = remaining === 0 || Number.isNaN(end);
            this.days = Math.floor(remaining / 86400);
            this.hours = pad(Math.floor((remaining % 86400) / 3600));
            this.minutes = pad(Math.floor((remaining % 3600) / 60));
            this.seconds = pad(remaining % 60);

            if (this.finished) {
                clearInterval(this.timer);
            }
        },

        get label() {
            if (this.finished) {
                return 'Período encerrado — atualize a página.';
            }

            const dayWord = this.days === 1 ? 'dia' : 'dias';

            return `Faltam ${this.days} ${dayWord}, ${this.hours}:${this.minutes}:${this.seconds}`;
        },
    };
}
