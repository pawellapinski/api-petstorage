@extends('layouts.app')

@section('content')
    <div class="row mb-4">
        <div class="col">
            <h1>Szczegóły zwierzęcia</h1>
        </div>
        <div class="col-auto">
            <div class="btn-group">
                <a href="{{ route('pets.index') }}" class="btn btn-secondary">Powrót</a>
                <a href="{{ route('pets.edit', $pet['id']) }}" class="btn btn-warning">Edytuj</a>
                <form action="{{ route('pets.destroy', $pet['id']) }}" method="POST" class="d-inline" onsubmit="return confirm('Czy na pewno chcesz usunąć to zwierzę?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Usuń</button>
                </form>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h5>Podstawowe informacje</h5>
                    <p><strong>ID:</strong> {{ $pet['id'] ?? 'Brak' }}</p>
                    <p><strong>Nazwa:</strong> {{ $pet['name'] ?? 'Brak' }}</p>
                    <p><strong>Status:</strong>
                        @if(isset($pet['status']))
                            @if($pet['status'] == 'available')
                                <span class="badge bg-success">Dostępny</span>
                            @elseif($pet['status'] == 'pending')
                                <span class="badge bg-warning">Oczekujący</span>
                            @elseif($pet['status'] == 'sold')
                                <span class="badge bg-danger">Sprzedany</span>
                            @else
                                <span class="badge bg-secondary">{{ $pet['status'] }}</span>
                            @endif
                        @else
                            <span class="badge bg-secondary">Brak</span>
                        @endif
                    </p>
                    <p><strong>Kategoria:</strong> {{ $pet['category']['name'] ?? 'Brak' }}</p>
                </div>

                <div class="col-md-6">
                    <h5>Tagi</h5>
                    @if(isset($pet['tags']) && is_array($pet['tags']) && count($pet['tags']) > 0)
                        @foreach($pet['tags'] as $tag)
                            <span class="badge bg-primary me-1">{{ $tag['name'] }}</span>
                        @endforeach
                    @else
                        <p>Brak tagów</p>
                    @endif

                    <h5 class="mt-3">Zdjęcia</h5>
                    @if(isset($pet['photoUrls']) && is_array($pet['photoUrls']) && count($pet['photoUrls']) > 0)
                        <div class="row g-2">
                            @foreach($pet['photoUrls'] as $url)
                                <div class="col-6 mb-3">
                                    <div class="card">
                                        <img
                                            src="{{ $url }}"
                                            alt="Zdjęcie zwierzęcia"
                                            class="img-fluid card-img-top"
                                            onerror="this.src='https://placehold.co/400x300?text=Brak+zdjęcia'"
                                            style="min-height: 200px; object-fit: cover;"
                                        >
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="alert alert-info">
                            <p class="mb-0">Brak dostępnych zdjęć dla tego zwierzęcia.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
