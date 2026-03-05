class OpenClawSystem {
    constructor() {
        this.sse = new EventSource('/server/nervous_system.php?action=stream');
        this.init();
    }
    init() {
        this.sse.onmessage = (e) => {
            const data = JSON.parse(e.data);
            if (data.event === 'PROGRESS') document.getElementById('progress-bar').style.width = data.val + '%';
            this.renderLog(data.msg);
        };
    }
    renderLog(msg) {
        const div = document.createElement('div');
        div.className = 'log-entry';
        div.innerHTML = `[${new Date().toLocaleTimeString()}] ${msg}`;
        const container = document.getElementById('log-stream');
        container.prepend(div);
    }
    async triggerTask(task) {
        this.renderLog("Initiating: " + task);
        await fetch('/server/nervous_system.php?action=queue&task=' + task);
    }
    async toggleSafety(sig) {
        await fetch('/server/nervous_system.php?action=safety&sig=' + sig);
        this.renderLog("SAFETY SIGNAL: " + sig.toUpperCase());
    }
}
const App = new OpenClawSystem();
