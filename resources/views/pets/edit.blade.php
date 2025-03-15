@extends('layouts.app')

@section('content')
    <div class="row mb-4">
        <div class="col">
            <h1>Edytuj zwierzę</h1>
        </div>
        <div class="col-auto">
            <div class="btn-group">
                <a href="{{ route('pets.index') }}" class="btn btn-secondary">Powrót</a>
                <a href="{{ route('pets.show', $pet['id']) }}" class="btn btn-info">Szczegóły</a>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('pets.update', $pet['id']) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label for="name" class="form-label">Nazwa*</label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $pet['name'] ?? '') }}" required>
                    @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="category_id" class="form-label">ID kategorii</label>
                            <input type="number" class="form-control @error('category_id') is-invalid @enderror" id="category_id" name="category_id" value="{{ old('category_id', $pet['category']['id'] ?? '') }}">
                            @error('category_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="category_name" class="form-label">Nazwa kategorii</label>
                            <input type="text" class="form-control @error('category_name') is-invalid @enderror" id="category_name" name="category_name" value="{{ old('category_name', $pet['category']['name'] ?? '') }}">
                            @error('category_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="status" class="form-label">Status*</label>
                    <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                        <option value="">Wybierz status</option>
                        <option value="available" {{ old('status', $pet['status'] ?? '') == 'available' ? 'selected' : '' }}>Dostępny</option>
                        <option value="pending" {{ old('status', $pet['status'] ?? '') == 'pending' ? 'selected' : '' }}>Oczekujący</option>
                        <option value="sold" {{ old('status', $pet['status'] ?? '') == 'sold' ? 'selected' : '' }}>Sprzedany</option>
                    </select>
                    @error('status')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="tags" class="form-label">Tagi (oddzielone przecinkami)</label>
                    @php
                        $tagString = '';
                        if(isset($pet['tags']) && is_array($pet['tags'])) {
                            $tagNames = array_map(function($tag) {
                                return $tag['name'] ?? '';
                            }, $pet['tags']);
                            $tagString = implode(', ', array_filter($tagNames));
                        }
                    @endphp
                    <input type="text" class="form-control @error('tags') is-invalid @enderror" id="tags" name="tags" value="{{ old('tags', $tagString) }}" placeholder="np. młody, przyjazny, szkolony">
                    @error('tags')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="form-text">Wprowadź tagi oddzielone przecinkami, np. "młody, przyjazny, szkolony"</div>
                </div>

                <div class="mb-3">
                    <label for="photoUrls" class="form-label">Adresy URL zdjęć (oddzielone przecinkami)</label>
                    @php
                        $photoUrlString = '';
                        if(isset($pet['photoUrls']) && is_array($pet['photoUrls'])) {
                            $photoUrlString = implode(', ', $pet['photoUrls']);
                        }
                    @endphp
                    <input type="text" class="form-control @error('photoUrls') is-invalid @enderror" id="photoUrls" name="photoUrls" value="{{ old('photoUrls', $photoUrlString) }}" placeholder="np. http://example.com/img1.jpg, http://example.com/img2.jpg">
                    @error('photoUrls')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                        <div class="form-text">Wprowadź adresy URL oddzielone przecinkami, np. "https://picsum.photos/id/237/200/300" Fskopiuj i sprawdź"</div>
                    </div>

                    <div class="text-end">
                        <button type="submit" class="btn btn-primary">Zapisz zmiany</button>
                    </div>
            </form>
        </div>
    </div>
@endsection
