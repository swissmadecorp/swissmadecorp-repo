<div
    data-visitor-monitor
    data-heartbeat-url="{{ url('/visitor-monitor/heartbeat') }}"
    data-leave-url="{{ url('/visitor-monitor/leave') }}"
    data-heartbeat-interval-ms="{{ config('visitor-monitor.heartbeat_interval_seconds', 15) * 1000 }}"
    class="hidden"
></div>
