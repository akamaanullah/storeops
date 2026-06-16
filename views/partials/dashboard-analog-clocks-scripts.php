<script>
(function() {
    function getZonedParts(timeZone) {
        const parts = new Intl.DateTimeFormat('en-US', {
            timeZone,
            hour: 'numeric',
            minute: 'numeric',
            second: 'numeric',
            hour12: false,
        }).formatToParts(new Date());

        const value = (type) => parseInt(parts.find((p) => p.type === type)?.value || '0', 10);
        let hour = value('hour');
        if (hour === 24) hour = 0;

        return {
            hour,
            minute: value('minute'),
            second: value('second'),
        };
    }

    function setHand(group, degrees) {
        if (group) {
            group.setAttribute('transform', `rotate(${degrees} 50 50)`);
        }
    }

    function formatDigital(timeZone) {
        return new Intl.DateTimeFormat('en-US', {
            timeZone,
            hour: 'numeric',
            minute: '2-digit',
            second: '2-digit',
            hour12: true,
        }).format(new Date());
    }

    function updateAnalogClocks() {
        document.querySelectorAll('.analog-clock-widget').forEach((widget) => {
            const tz = widget.dataset.tz;
            if (!tz) return;

            const { hour, minute, second } = getZonedParts(tz);
            const svg = widget.querySelector('svg');
            if (!svg) return;

            setHand(svg.querySelector('[data-hand="hour"]'), ((hour % 12) + minute / 60) / 12 * 360);
            setHand(svg.querySelector('[data-hand="minute"]'), (minute + second / 60) / 60 * 360);
            setHand(svg.querySelector('[data-hand="second"]'), (second / 60) * 360);

            const digital = document.getElementById(widget.dataset.digitalId);
            if (digital) {
                digital.textContent = formatDigital(tz);
            }
        });
    }

    updateAnalogClocks();
    setInterval(updateAnalogClocks, 1000);
})();
</script>
