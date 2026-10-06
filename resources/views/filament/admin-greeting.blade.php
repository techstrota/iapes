<div id="fi-greeting-wrap" style="
    display: inline-flex;
    align-items: center;
    gap: 8px;
    height: 100%;
    padding: 0 0.75rem;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
    font-size: 0.85rem;
    font-weight: 600;
    color: #dae2fd;
    letter-spacing: 0.01em;
    white-space: nowrap;
">
    <span style="color: #8e909f; font-weight: 500;">Signed in as</span>
    <span style="color: #ffffff; font-weight: 700; background-color: rgba(30, 64, 175, 0.25); border: 1px solid rgba(59, 130, 246, 0.35); padding: 3px 9px; border-radius: 9999px; display: inline-flex; align-items: center; gap: 5px;">
        <span style="width: 6px; height: 6px; border-radius: 50%; background-color: #10b981; display: inline-block;"></span>
        {{ auth()->user()?->name ?? 'Admin' }}
    </span>
</div>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        function fixOrder() {
            const greeting = document.getElementById("fi-greeting-wrap");
            const userMenu = document.querySelector(".fi-user-menu");
            if (!greeting || !userMenu) return;

            const parent = userMenu.parentElement;
            if (!parent) return;

            parent.insertBefore(greeting, userMenu);
        }
        fixOrder();
        setTimeout(fixOrder, 300);
    });
</script>
