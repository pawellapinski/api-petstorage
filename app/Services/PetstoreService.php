<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PetstoreService
{
    protected string $baseUrl = 'https://petstore.swagger.io/v2';

    public function getAllPets()
    {
        try {
            $response = Http::withoutVerifying()
                ->get("{$this->baseUrl}/pet/findByStatus", [
                    'status' => 'available'
                ]);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Failed to fetch pets', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('Error fetching pets', [
                'exception' => $e->getMessage()
            ]);
            return null;
        }
    }

    public function getPet(int $id)
    {
        try {
            $response = Http::withoutVerifying()
                ->get("{$this->baseUrl}/pet/{$id}");

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Failed to fetch pet', [
                'id' => $id,
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('Error fetching pet', ['id' => $id, 'exception' => $e->getMessage()]);
            return null;
        }
    }


   /* public function createPet(array $data)
    {
        try {
            $response = Http::withoutVerifying()
                ->post("{$this->baseUrl}/pet", $data);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Failed to create pet', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('Error creating pet', ['exception' => $e->getMessage()]);
            return null;
        }
    }*/

    public function createPet(array $data)
    {
        try {
            Log::info('Próba utworzenia zwierzęcia', ['data' => $data]);

            $response = Http::withoutVerifying()
                ->post("{$this->baseUrl}/pet", $data);

            Log::info('Odpowiedź z API przy tworzeniu', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Failed to create pet', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('Error creating pet', ['exception' => $e->getMessage()]);
            return null;
        }
    }


    public function updatePet(array $data)
    {
        try {
            $response = Http::withoutVerifying()
                ->put("{$this->baseUrl}/pet", $data);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Failed to update pet', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('Error updating pet', ['exception' => $e->getMessage()]);
            return null;
        }
    }

    public function deletePet(int $id)
    {
        try {
            $response = Http::withoutVerifying()
                ->delete("{$this->baseUrl}/pet/{$id}");

            if ($response->successful()) {
                return true;
            }

            Log::error('Failed to delete pet', [
                'id' => $id,
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            return false;
        } catch (\Exception $e) {
            Log::error('Error deleting pet', ['id' => $id, 'exception' => $e->getMessage()]);
            return false;
        }
    }
}
