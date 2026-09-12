<div id="fi-greeting-wrap" style="
    display: flex;
    align-items: center;
    height: 100%;
    padding: 0 0.5rem 0 0;
    font-family: Poppins, sans-serif;
    font-size: 0.95rem;
    font-weight: 600;
    color: #2a4795;
    letter-spacing: 0.01em;
    white-space: nowrap;
">
    <span style="color: #1d70b8;">Hello..!! {{ auth('intern')->user()?->name ? explode(' ', auth('intern')->user()->name)[0] : 'Intern' }}</span>&nbsp;??
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
