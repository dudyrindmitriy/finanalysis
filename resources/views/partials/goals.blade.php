<article class="liquid-glass-card">
    <div class="goal-form">
        <form id="create-goal-form" data-goalcreate-route="{{ route('goals.store') }}">
            @csrf
            <input type="text" name="name" placeholder="Название цели" required>
            <input type="number" name="target_amount" placeholder="Целевая сумма" step="0.01" min="0.01" max="99999999.99"
                required>
            <input type="date" name="deadline" required>
            <button type="submit">Создать цель</button>
        </form>
    </div>
</article>
<div class="goals-list">
    @foreach($goals as $goal)
        <article class="liquid-glass-card">

            <div class="goal-item">
                <div class="goal-header">
                    <h3>{{ $goal->name }}</h3>
                    <span class="goal-deadline">до {{ $goal->deadline }}</span>
                </div>

                <div class="goal-progress">
                    <div class="progress-bar">
                        <div class="progress-fill"
                            style="width: {{ $goal->target_amount > 0 ? ($goal->current_amount / $goal->target_amount) * 100 : 0 }}%">
                        </div>
                    </div>
                    <div class="goal-amounts">
                        <span>{{ number_format($goal->current_amount, 2) }} ₽</span>
                        <span>/</span>
                        <span>{{ number_format($goal->target_amount, 2) }} ₽</span>
                        <span>({{ $goal->target_amount > 0 ? round(($goal->current_amount / $goal->target_amount) * 100, 1) : 0 }}%)</span>
                    </div>
                </div>

                <div class="goal-actions">
                    <input type="number" class="add-money-input" placeholder="Сумма" step="0.01" min="0.01" max="99999999.99"
                        data-goal-id="{{ $goal->id }}">
                    <button type="submit" class="add-money-btn" data-goal-id="{{ $goal->id }}" data-addmoney-route = "{{route("goals.add-money")}}">Добавить</button>
                </div>
            </div>
        </article>

    @endforeach
</div>

<style>
    .goal-header {
        display: flex;
        justify-content: space-between;
    }

    .goal-progress {
        margin: 1rem 0;
    }

    .goal-amounts {
        display: flex;
        gap: 5px;
        margin-top: 5px;
        font-size: 0.9rem;
    }


    .progress-bar {
        width: 100%;
        height: 8px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 4px;
    }

    .progress-fill {
        height: 100%;
        background: var(--pico-primary);
        border-radius: 4px;
        transition: width 0.3s;
    }
</style>
