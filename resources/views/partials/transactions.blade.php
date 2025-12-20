<article class="liquid-glass-card">
    <ul class="transaction-list">
        @forelse ($transactions as $transaction)
            <li>
                <span class="category-badge">
                    <span class="category-icon" style="background-color: {{ $transaction->category->color }};">
                        {{ $transaction->category->getAbbreviationAttribute() }}
                    </span>
                </span>
                <span class="transaction-description" title="{{ $transaction->description }}">
                    {{ $transaction->description ?: 'Без описания' }}
                </span>
                <span class="{{ $transaction->type === 'income' ? 'success' : 'danger' }}">
                    {{ $transaction->type === 'income' ? '+' : '-' }}
                    ₽{{ number_format($transaction->amount, 2) }}
                </span>
            </li>
        @empty
            <li>Транзакций пока нет.</li>
        @endforelse
    </ul>
    <div class='pagination'>
        <button type="button" class="next-page"><svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e3e3e3"><path d="M480-360 280-560h400L480-360Z"/></svg></button>
    </div>
    {{$transactions->links()}}
</article>
