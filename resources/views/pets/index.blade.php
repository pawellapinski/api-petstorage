@extends('layouts.app')

@section('content')
    <div class="row mb-4">
        <div class="col">
            <h1>Lista zwierząt</h1>
        </div>
        <div class="col-auto">
            <a href="{{ route('pets.create') }}" class="btn btn-primary">Dodaj zwierzę</a>
        </div>
    </div>
    <div class="row mb-3">
        <div class="col-md-4">
            <form action="{{ route('pets.index') }}" method="GET" class="d-flex">
                <input type="number" name="search_id" class="form-control me-2" placeholder="Wyszukaj po ID..." value="{{ request('search_id') }}">
                <button type="submit" class="btn btn-primary">Szukaj</button>
                @if(request('search_id'))
                    <a href="{{ route('pets.index') }}" class="btn btn-secondary ms-2">Wyczyść</a>
                @endif
            </form>
        </div>
    </div>

    @if(isset($pets) && !empty($pets))
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                <tr>
                    <th>
                        <a href="{{ request()->fullUrlWithQuery(['direction' => request('direction') === 'asc' ? 'desc' : 'asc']) }}" class="text-dark text-decoration-none">
                            ID
                            @if(request('direction') === 'asc')
                                ↑
                            @else
                                ↓
                            @endif
                        </a>
                    </th>
                    <th>Nazwa</th>
                    <th>Kategoria</th>
                    <th>Status</th>
                    <th>Akcje</th>
                </tr>
                </thead>
                <tbody>
                @foreach($pets as $pet)
                    <tr>
                        <td>{{ $pet['id'] ?? 'Brak' }}</td>
                        <td>{{ $pet['name'] ?? 'Brak' }}</td>
                        <td>
                            @if(isset($pet['category']) && isset($pet['category']['name']) && $pet['category']['name'] !== 'string')
                                {{ $pet['category']['name'] }}
                            @else
                                Brak kategorii
                            @endif
                        </td>

                        <td>
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
                        </td>
                        <td>
                            <div class="btn-group">
                                <a href="{{ route('pets.show', $pet['id']) }}" class="btn btn-sm btn-info">Szczegóły</a>
                                <a href="{{ route('pets.edit', $pet['id']) }}" class="btn btn-sm btn-warning">Edytuj</a>
                                <form action="{{ route('pets.destroy', $pet['id']) }}" method="POST" class="d-inline" onsubmit="return confirm('Czy na pewno chcesz usunąć to zwierzę?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">Usuń</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            <div class="d-flex justify-content-center">
                {{ $pets->links() }}
            </div>
        </div>
    @else
        <div class="alert alert-info">
            Brak zwierząt do wyświetlenia lub wystąpił problem z ich pobraniem.
        </div>
    @endif
@endsection
