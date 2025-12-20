@extends('layouts.app')
<style>
    .card {
        height: 90vh !important;
    }

    article {
        padding: 0 !important;
    }

    .charts-container {
        display: flex;
        gap: 20px;
        margin-bottom: 20px;
    }

    .chart-wrapper {
        flex: 1;
        min-height: 300px;
    }

    .chart-wrapper canvas {
        width: 100% !important;
        height: 300px !important;
    }
</style>
@section('content')
    <div class="grid">
        <div class="sidebar liquid-glass-card">
            <div class="content">
                <nav class="main-nav"> <a href="{{ route('dashboard') }}" data-route='{{ route('dashboard') }}'
                        class="nav-link active" data-page='dashboard'>
                        <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px"
                            fill="#e3e3e3">
                            <path
                                d="M240-200h120v-240h240v240h120v-360L480-740 240-560v360Zm-80 80v-480l320-240 320 240v480H520v-240h-80v240H160Zm320-350Z" />
                        </svg>
                        Домой
                    </a>

                    <a href="{{ route('transactions') }}" data-route='{{ route('transactions') }}' class="nav-link"
                        data-page="transactions">
                        <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px"
                            fill="#e3e3e3">
                            <path
                                d="M480-120q-138 0-240.5-91.5T122-440h82q14 104 92.5 172T480-200q117 0 198.5-81.5T760-480q0-117-81.5-198.5T480-760q-69 0-129 32t-101 88h110v80H120v-240h80v94q51-64 124.5-99T480-840q75 0 140.5 28.5t114 77q48.5 48.5 77 114T840-480q0 75-28.5 140.5t-77 114q-48.5 48.5-114 77T480-120Zm112-192L440-464v-216h80v184l128 128-56 56Z" />
                        </svg>
                        Транзакции
                    </a>
                    <a href="{{ route('goals') }}" data-route='{{ route('goals') }}' class="nav-link" data-page="goals">
                        <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px"
                            fill="#e3e3e3">
                            <path
                                d="M200-80v-760h640l-80 200 80 200H280v360h-80Zm80-440h442l-48-120 48-120H280v240Zm0 0v-240 240Z" />
                        </svg>
                        Цели
                    </a>
                    <a href="{{ route('import') }}" data-route='{{ route('import') }}' class="nav-link" data-page="import">
                        <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px"
                            fill="#e3e3e3">
                            <path
                                d="M440-320v-326L336-542l-56-58 200-200 200 200-56 58-104-104v326h-80ZM240-160q-33 0-56.5-23.5T160-240v-120h80v120h480v-120h80v120q0 33-23.5 56.5T720-160H240Z" />
                        </svg>
                        Импорт
                    </a>

                </nav>
            </div>
        </div>
        <div class="dashboard-content-area">
            {{-- @include('partials.dashboard') --}}
        </div>
        <div class="liquid-glass-card chat-container">
            @include('partials.chat')

        </div>
    </div>
@endsection
