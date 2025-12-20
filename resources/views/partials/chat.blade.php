<div class="chat-history" id="chat-history">
    @if (!empty($chatHistory))
        @foreach ($chatHistory as $index => $chat)
            @if ($chat['role'] == 'USER')
                <div class="message user-message">
                    <div class="message-content">
                        <div class="message-text">{{ $chat['message'] }}</div>
                    </div>
                </div>
            @endif

            @if ($chat['role'] == 'CHATBOT')
                <div class="message assistant-message">
                    <div class="message-content">
                        <div class="message-text">{{ $chat['message'] }}</div>
                    </div>
                </div>
            @endif
        @endforeach
    @endif

</div>
<div class="chat-input-container">
    <form id="chat-form" class="chat-form" data-ask-ai-route="{{ route('ask-ai') }}">
        @csrf
        <textarea name="message" id="chat-message" rows="1" placeholder="Сообщение..." required></textarea>
        <button type="submit" id="send-button"></button>
    </form>
</div>
