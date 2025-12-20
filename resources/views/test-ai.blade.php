<form id="chat-form" method="POST" class="chat-form">
    @csrf
    <div class="input-group">
        <textarea name="message" id="chat-message" rows="3" placeholder="Задайте вопрос по своим финансам..." required></textarea>
    </div>
    <div>
        <button type="submit" id="send-button">Отправить запрос</button>
    </div>
</form>
<script>
    document.getElementById('chat-form').addEventListener('submit', function(e) {
        e.preventDefault();
        const messageInput = document.getElementById('chat-message');
        const userMessage = messageInput.value.trim();

        if (!userMessage) return;

        const payload = {
            message: userMessage,
        };

        fetch('{{ route('ask-ai') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                },
                body: JSON.stringify(payload)
            })
            .then(function(result) {
                console.log(result);
            })
            .catch(function(error) {
                console.error(error);
            })
    });
</script>
