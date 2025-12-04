<a href="{{ route('parse-form') }}">Протестиовать парсер</a>
<a href="{{ route('logout') }}">Выйти</a>
<form id="chat-form">
    @csrf
    <div>
        <textarea name="message" id="chat-message" rows="3" placeholder="Задайте вопрос по своим финансам..." required></textarea>
    </div>
    <div>
        <button type="submit" id="send-button">Отправить запрос</button>
    </div>
</form>
<div class="answer"></div>
<script>
    document.getElementById('chat-form').addEventListener('submit', function(e) {
        e.preventDefault(); // Останавливаем стандартную отправку формы

        // 1. Получаем элементы и данные
        const messageInput = document.getElementById('chat-message');
        const sendButton = document.getElementById('send-button');
        const userMessage = messageInput.value.trim();
        const answerDiv = document.querySelector('.answer');

        if (!userMessage) return; // Не отправляем пустое сообщение

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
            // 7. Обработка общих ошибок (сети, проблем с JSON)
            .catch(function(error) {
            })
    });
</script>
