</main> </div> </div> <audio id="notification-sound" src="../assets/sounds/notification.mp3" preload="auto"></audio>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const bell = document.getElementById('notification-bell');
    const badge = document.getElementById('notification-badge');
    const sound = document.getElementById('notification-sound');
    const initialTitle = document.title;
    let lastKnownCount = 0;

    function checkNotifications() {
        fetch('../api/notifications_handler.php').then(response => response.json()).then(data => {
            const newCount = data.new_orders_count;
            if (newCount > 0) {
                badge.textContent = newCount;
                if (!badge.classList.contains('active')) {
                     badge.classList.remove('hidden');
                     badge.classList.add('active');
                }
                if (newCount > lastKnownCount) {
                    sound.play().catch(e => console.warn("Interação do usuário necessária para tocar o som."));
                    document.title = `(${newCount}) Nova OS! - ` + initialTitle;
                }
            } else {
                badge.classList.add('hidden');
                badge.classList.remove('active');
                if (document.title.startsWith('(')) {
                    document.title = initialTitle;
                }
            }
            lastKnownCount = newCount;
        }).catch(error => console.error('Erro ao verificar notificações:', error));
    }

    bell.addEventListener('click', (e) => {
        e.preventDefault();
        fetch('../api/notifications_handler.php?clear=true').then(response => response.json()).then(result => {
            if (result.success) {
                lastKnownCount = 0;
                badge.classList.remove('active');
                badge.classList.add('hidden');
                document.title = initialTitle;
                window.location.href = 'os_list.php?status=Aguardando Chegada';
            }
        }).catch(error => console.error('Erro ao limpar notificações:', error));
    });

    setInterval(checkNotifications, 15000); // Verifica a cada 15 segundos
    checkNotifications();

    const pageH1 = document.querySelector('main h1');
    const titleContainer = document.getElementById('page-title-container');
    if (pageH1 && titleContainer) {
        titleContainer.appendChild(pageH1);
    }
});
</script>
</body>
</html>